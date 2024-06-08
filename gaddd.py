import random
from fit_pair import Pair

class GenAlgo:

    def fitness(chrom, trucks):
        fitness = 0
        for truck in trucks:
            truck.fit = 0
        
        for truck, items in zip(trucks, chrom):
            truck.reset()
            for item in items:
                if truck.can_accommodate(item):
                    truck.add_item(item)
                    truck.fit += item.weight * item.dest.distance * item.volume
                    
            distance = sum(route.dest.distance for route in truck.route)
            truck.fit -= (distance / (truck.fuel * truck.km_liter))

        for truck, items in zip (trucks, chrom):
            fitness += truck.fit

        return fitness

    def pair(population, fitness):
        pairs = []
        for i in range(len(population)):
            if fitness is None:
                solution = Pair(population[i], None)
            else : 
                if not isinstance(population[i], list):
                    population[i] = population[i].get_chrom()
                solution = Pair(population[i], fitness[i])
            pairs.append(solution)
        return pairs

    def mutate(chrom, products):
        mutated_chrom = chrom[:]  
        mutation_type = random.choice(["single", "double", "random"])
        if mutation_type == "single":
            selected_truck = random.choice(mutated_chrom)
            if len(selected_truck) >= 2:
                idx1, idx2 = random.sample(range(len(selected_truck)), 2)
                selected_truck[idx1], selected_truck[idx2] = selected_truck[idx2], selected_truck[idx1]

        elif mutation_type == "double":
            idx1, idx2 = random.sample(range(len(mutated_chrom)), 2)
            truck1, truck2 = mutated_chrom[idx1], mutated_chrom[idx2]
            if truck1 and truck2:  
                item1 = random.choice(truck1)
                item2 = random.choice(truck2)
                if item1 not in truck2 and item2 not in truck1:  
                    truck1[truck1.index(item1)], truck2[truck2.index(item2)] = item2, item1
        else:
            idx1, idx2 = random.sample(range(len(mutated_chrom)), 2)
            truck1, truck2 = mutated_chrom[idx1], mutated_chrom[idx2]

            if truck1 and truck2:
                idx_t1 = random.randint(0, len(truck1) - 1)
                idx_t2 = random.randint(0, len(truck2) - 1)

                t1 = set(truck1)
                t2 = set(truck2)

                t1_none = [item for truck in mutated_chrom for item in truck if item not in t1]
                t2_none = [item for truck in mutated_chrom for item in truck if item not in t2]

                if t1_none and t2_none:
                    item1 = random.choice(t1_none)
                    item2 = random.choice(t2_none)

                    truck1[idx_t1], truck2[idx_t2] = item1, item2

        
        return mutated_chrom

    def crossover(parent1, parent2, parent3):

        # uniform
        child1 = []
        child2 = []
        child3 = []

        for i in range(min(len(parent1), len(parent2), len(parent3))):
            rand = random.random()
            if rand < 0.33:
                child1.append(parent1[i])
                child2.append(parent2[i])
                child3.append(parent3[i])
            elif rand >= 0.33 and rand < 0.66:
                child1.append(parent3[i])
                child2.append(parent1[i])
                child3.append(parent2[i])
            else:
                child1.append(parent2[i])
                child2.append(parent3[i])
                child3.append(parent1[i])

        return child1, child2, child3

    def genetic_algorithm(population_size, num_generations, trucks, products):
        best = None
        population = [GenAlgo.init_population(len(trucks), products) for _ in range(population_size)]
        population = GenAlgo.pair(population, None)
        
        for generation in range(num_generations):
            fitness_values = [GenAlgo.fitness(chrom.chrom, trucks) for chrom in population]
            pairs = GenAlgo.pair(population, fitness_values)

            selected_parents = GenAlgo.selection(pairs, tournament_size=5)
            
            offspring = []
            for i in range(0, len(selected_parents)-2, 3):
                parent1, parent2, parent3 = selected_parents[i].chrom, selected_parents[i+1].chrom, selected_parents[i+2].chrom
                child1, child2, child3 = GenAlgo.crossover(parent1, parent2, parent3)
                child1 = GenAlgo.mutate(child1, products)
                child2 = GenAlgo.mutate(child2, products)
                child3 = GenAlgo.mutate(child3, products)
                offspring.extend([child1, child2, child3])
                
            mutated_offspring = [GenAlgo.mutate(chrom, products) for chrom in offspring]
            mutated_fitval = [GenAlgo.fitness(chrom, trucks) for chrom in mutated_offspring]
            mut_pairs = GenAlgo.pair(mutated_offspring, mutated_fitval)

            best = max(mut_pairs, key=lambda chrom: chrom.fitness)
            if GenAlgo.check_valid(best.get_chrom(), trucks) == 0:
                return best
            
            best_individual = sorted(pairs, key=lambda chrom: chrom.fitness, reverse=True)[:5]
            population = best_individual + mut_pairs[:-1]

        best_individuals = sorted(population, key=lambda chrom: chrom.fitness, reverse=True)

        return best_individuals

    def init_population(num_trucks, products):
        return [random.sample(products, random.randint(1, len(products))) for _ in range(num_trucks)]

    def selection(pairs, tournament_size):
        selected_parents = []
        for _ in range(len(pairs)):
            tournament = random.sample(pairs, tournament_size)
            winner = max(tournament, key=lambda x: x.fitness)
            selected_parents.append(winner)
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
