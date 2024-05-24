import numpy as np

class PSO:
    def initialize_particles(num_particles, num_products, num_trucks):
        particles = []
        for _ in range(num_particles):
            particle = np.random.randint(-1, num_trucks, num_products)
            particles.append(particle)
        return particles

    def calculate_fitness(particle, products, trucks):
        fitness = 0
        for truck in trucks:
            truck.reset()
            truck.fit = 0

        for product_idx, truck_idx in enumerate(particle):
            if truck_idx >= 0:
                product = products[product_idx]
                truck = trucks[truck_idx]
                if truck.can_accommodate(product):
                    truck.add_item(product)
                    truck.fit += product.weight * product.dest.distance * product.volume
                else:
                    truck_idx = -1

            fitness -= 100000 if truck_idx == -1 else 0

        for truck in trucks:
            if truck.route:
                distance = sum(route.dest.distance for route in truck.route)
                truck.fit -= (distance / (truck.fuel * truck.km_liter))
                if distance/truck.km_liter > (truck.fuel - 20):
                    truck.fit *= 0.5
            fitness+=truck.fit

        return fitness

    def pso(trucks, products, num_particles=30, iterations=100, c1=2.8, c2=1.3):
        num_products = len(products)
        num_trucks = len(trucks)
        particles = PSO.initialize_particles(num_particles, num_products, num_trucks)
        velocities = [np.zeros(num_products) for _ in range(num_particles)]
        
        p_best_positions = particles.copy()
        p_best_scores = [PSO.calculate_fitness(p, products, trucks) for p in particles]
        g_best_position = p_best_positions[np.argmax(p_best_scores)]
        g_best_score = max(p_best_scores)

        phi = c1 + c2
        w = 2 / abs(2 - phi - np.sqrt((phi**2) - (4 * phi)))

        unique_solutions = set(tuple(p) for p in particles)
        while len(unique_solutions) < num_particles:
            new_particle = np.random.randint(-1, num_trucks, num_products)
            if tuple(new_particle) not in unique_solutions:
                unique_solutions.add(tuple(new_particle))
                particles.append(new_particle)
                velocities.append(np.zeros(num_products))
                p_best_positions.append(new_particle)
                p_best_scores.append(PSO.calculate_fitness(new_particle, products, trucks))
        
        for _ in range(iterations):
            for i in range(num_particles):
                r1 = np.random.random(num_products)
                r2 = np.random.random(num_products)
                velocities[i] = (w * velocities[i] + 
                                c1 * r1 * (p_best_positions[i] - particles[i]) + 
                                c2 * r2 * (g_best_position - particles[i]))
                particles[i] = np.clip(particles[i] + velocities[i], -1, num_trucks - 1).astype(int)
                fitness = PSO.calculate_fitness(particles[i], products, trucks)
                
                if fitness > p_best_scores[i]:
                    p_best_positions[i] = particles[i]
                    p_best_scores[i] = fitness
            
            w *= 0.99

            g_best_positions = []
            g_best_scores = []
            sorted_indices = np.argsort(p_best_scores)[::-1][:5]
            for idx in sorted_indices:
                if p_best_scores[idx] > g_best_score and not any((p_best_positions[idx] == x).all() for x in g_best_positions):
                    g_best_positions.append(p_best_positions[idx])
                    g_best_scores.append(p_best_scores[idx])
                if len(g_best_positions) >= 3:
                    break
            g_best_score = np.max(sorted_indices)
        
        return g_best_positions