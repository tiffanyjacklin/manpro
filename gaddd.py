import random

class GenAlgo:

    def fitness(chrom, trucks):
        total_distance = 0
        # print(chrom, type(chrom))
        # for truck in trucks:
        #     truck.weight = 0
        #     truck.route = []
        #     truck_fuel = truck.fuel
        #     truck_vol = truck.volume
        
        for truck, items in zip(trucks, chrom):
            truck.weight = 0
            truck.route = []
            truck_fuel = truck.fuel - 20
            truck_vol = truck.volume - 20
            for item in items:
                # print("weight: ", truck.weight, item.weight, truck.max_weight)
                # print("len: ", truck.length, item.length)
                # print("width: ", truck.width, item.width)
                # print("height: ", truck.height, item.height)
                # print("fuel: ", truck.fuel, item.dest.distance)
                # print("route: ", truck.route)
                if (truck.weight + item.weight < truck.max_weight and
                    truck_vol > item.volume and
                    truck_fuel > (item.dest.distance/truck.km_liter) and
                    item not in truck.route):

                    truck.weight += item.weight
                    truck.route.append(item)
                    truck_vol -= item.volume

                    truck.cost += item.weight * item.dest.distance * item.volume
                    truck_fuel -= item.dest.distance/truck.km_liter

                    # total_distance += item.dest.distance
            # print(truck.route[0])
            for route in truck.route:
                truck.cost -= (route.dest.distance / truck.fuel)

        for truck, items in zip (trucks, chrom):
            total_distance += truck.cost

        # print("END___________________________________________")
        return 1/abs(total_distance)


    def mutate(chrom, products):
        mutated_chrom = chrom[:]  # Copy the chromosome to avoid modifying the original
        mutation_type = random.choice(["single", "double"])
        if mutation_type == "single":
            selected_truck = random.choice(mutated_chrom)
            if len(selected_truck) >= 2:
                idx1, idx2 = random.sample(range(len(selected_truck)), 2)
                selected_truck[idx1], selected_truck[idx2] = selected_truck[idx2], selected_truck[idx1]

        else:
            idx1, idx2 = random.sample(range(len(mutated_chrom)), 2)
            truck1, truck2 = mutated_chrom[idx1], mutated_chrom[idx2]
            if truck1 and truck2:  # Make sure both trucks are not empty
                item1 = random.choice(truck1)
                item2 = random.choice(truck2)
                if item1 not in truck2 and item2 not in truck1:  # Ensure items are not already in the other truck
                    truck1[truck1.index(item1)], truck2[truck2.index(item2)] = item2, item1
        
        return mutated_chrom

    def crossover(parent1, parent2):
        crossover_point = random.randint(1, min(len(parent1), len(parent2)) - 1)
        
        child1 = parent1[:crossover_point] + parent2[crossover_point:]
        child2 = parent2[:crossover_point] + parent1[crossover_point:]

        # crossover two points
        # crossover_points = sorted(random.sample(range(1, min(len(parent1), len(parent2)) - 1), 2))
        # crossover_point1, crossover_point2 = crossover_points

        # child1 = parent1[:crossover_point1] + parent2[crossover_point1:crossover_point2] + parent1[crossover_point2:]
        # child2 = parent2[:crossover_point1] + parent1[crossover_point1:crossover_point2] + parent2[crossover_point2:]

        # if child1 == parent1 or child2 == parent2: print(True)
        # else: print(False)
        return child1, child2


    def genetic_algorithm(population_size, num_generations, trucks, products):
        best = None
        population = [GenAlgo.init_population(len(trucks), products) for _ in range(population_size)]
        # for i, sub_array_2d in enumerate(population):
        #     print(f"Layer {i}:")
        #     for j, sub_array_1d in enumerate(sub_array_2d):
        #         print(f"  Row {j}:")
        #         for k, item in enumerate(sub_array_1d):
        #             print(f"    Object ID: {item.id}, Value: {item}")
        
        for generation in range(num_generations):
            # print(population)
            # Evaluate fitness for each individual in the population
            fitness_values = [GenAlgo.fitness(chrom, trucks) for chrom in population]
            # print(fitness_values)
            # Select parents for crossover using tournament selection
            selected_parents = GenAlgo.selection(population, fitness_values, tournament_size=5)
            # selected_parents = GenAlgo.selection(pairs)
            # for i, sub_array_2d in enumerate(selected_parents):
            #     print(f"Layer {i}:")
            #     for j, sub_array_1d in enumerate(sub_array_2d):
            #         print(f"  Row {j}:")
            #         for k, item in enumerate(sub_array_1d):
            #             print(f"    Object ID: {item.id}, Value: {item}")
            
            # Perform crossover to generate offspring
            offspring = []
            for i in range(0, len(selected_parents), 2):
                parent1, parent2 = selected_parents[i], selected_parents[i+1]
                child1, child2 = GenAlgo.crossover(parent1, parent2)
                child1 = GenAlgo.mutate(child1, products)
                child2 = GenAlgo.mutate(child2, products)
                offspring.extend([child1, child2])
                
            # Apply mutation to offspring
            mutated_offspring = [GenAlgo.mutate(chrom, products) for chrom in offspring]

            # best_mutated = mutated_offspring[mutated_fitness_values.index(min(mutated_fitness_values))]
            # Replace old population with new population (elitism: keep the best individual)
            best = min(population, key=lambda chrom: GenAlgo.fitness(chrom, trucks))
            if GenAlgo.check_valid(best, trucks) == 0:
                best_individual = best
                best_fitness = GenAlgo.fitness(best_individual, trucks)
                return best_individual, best_fitness
            
            old = population[:]
            # population = [min(population, key=lambda chrom: GenAlgo.fitness(chrom, trucks))] + mutated_offspring[:-1]
            best_individuals = sorted(population, key=lambda chrom: GenAlgo.fitness(chrom, trucks))[:5]
            population = best_individuals + mutated_offspring[:-1]
            
        # best_individual = min(population, key=lambda chrom: GenAlgo.fitness(chrom, trucks))
        # best_fitness = GenAlgo.fitness(best_individual, trucks)
        sorted_population = sorted(population, key=lambda chrom: GenAlgo.fitness(chrom, trucks))
        best_individuals = []
        j = None
        for i in sorted_population:
            if i == j:
                print(True)
            else: print(False)

            j = i

        for individual in sorted_population:
            if individual not in best_individuals and len(best_individuals) < 3:
                best_individuals.append(individual)
            elif len(best_individuals) == 3:
                break

        best_fitness = [GenAlgo.fitness(chrom, trucks) for chrom in best_individuals]
        return best_individuals, best_fitness

    def init_population(num_trucks, products):
        return [random.sample(products, random.randint(1, len(products))) for _ in range(num_trucks)]

    def selection(population, fitness_values, tournament_size):
        # for i, sub_array_2d in enumerate(population):
        #     print(f"Layer {i}:")
        #     for j, sub_array_1d in enumerate(sub_array_2d):
        #         print(f"  Row {j}:")
        #         for k, item in enumerate(sub_array_1d):
        #             print(f"    Object ID: {item.id}, Value: {item}")
        selected_parents = []
        for _ in range(len(population)):
            tournament = random.sample(range(len(population)), tournament_size)
            winner = min(tournament, key=lambda x: fitness_values[x])
            selected_parents.append(population[winner])
        # print(selected_parents)
        return selected_parents

    def check_valid(pop, trucks):
        violate = 0
        for truck in pop:
            item_w = 0
            item_v = 0
            item_d = 0
            for item in truck:
                 item_w += item.weight
                 item_v += item.volume
                 item_d += item.dest.distance
            i = pop.index(truck)
            if item_v > (trucks[i].volume - 20) or item_d > (trucks[i].fuel - 20) or item_w >= trucks[i].max_weight:
                violate += 1
        return violate
