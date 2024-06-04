import mysql.connector
import random
import numpy as np
import math

# Connect to the database
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="logistics_company"
)

cursor = db.cursor(dictionary=True)

# Fetch available trucks not currently delivering
def fetch_available_trucks():
    query = """
    SELECT t.id, t.unique_number, t.total_distance, t.truck_status
    FROM truck t
    LEFT JOIN truck_driver td ON t.id = td.id_truck
    LEFT JOIN schedule s ON td.id = s.id_schedule
    WHERE (s.status IS NULL OR s.status != 1) AND t.truck_status = 1
    """
    cursor.execute(query)
    return cursor.fetchall()

# Define the individual and population
def create_individual(trucks, min_trucks):
    return [random.choice(trucks) for _ in range(min_trucks)]

def create_population(trucks, min_trucks, n):
    return [create_individual(trucks, min_trucks) for _ in range(n)]

# Evaluate the fitness of an individual
def eval_truck_usage(individual):
    return sum(truck['total_distance'] for truck in individual),

# Crossover operation
def mate(ind1, ind2):
    size = min(len(ind1), len(ind2))
    cxpoint1, cxpoint2 = sorted(random.sample(range(size), 2))
    ind1[cxpoint1:cxpoint2], ind2[cxpoint1:cxpoint2] = ind2[cxpoint1:cxpoint2], ind1[cxpoint1:cxpoint2]
    return ind1, ind2

# Mutation operation
def mutate(individual, trucks):
    index = random.randrange(len(individual))
    individual[index] = random.choice(trucks)
    return individual,

# Selection operation
def select(population, k, tournsize=3):
    chosen = []
    for _ in range(k):
        aspirants = [random.choice(population) for _ in range(tournsize)]
        chosen.append(min(aspirants, key=lambda ind: eval_truck_usage(ind)))
    return chosen

# Check for duplicate Truck IDs
def has_duplicates(individual):
    truck_ids = [truck['id'] for truck in individual]
    return len(truck_ids) != len(set(truck_ids))

# Genetic Algorithm main loop
def main():
    trucks = fetch_available_trucks()
    min_trucks = max(3, math.ceil(len(trucks) * 0.25))

    if len(trucks) <= 3:
        output = [{"Truck ID": truck['id'], "Unique Number": truck['unique_number']} for truck in trucks]
    else:
        while True:
            population = create_population(trucks, min_trucks, 50)
            n_gen = 40
            cxpb, mutpb = 0.5, 0.2

            for gen in range(n_gen):
                # Evaluate the population
                fitnesses = list(map(eval_truck_usage, population))

                # Select the next generation individuals
                offspring = select(population, len(population))
                # Clone the selected individuals
                offspring = list(map(list, offspring))

                # Apply crossover and mutation
                for child1, child2 in zip(offspring[::2], offspring[1::2]):
                    if random.random() < cxpb:
                        mate(child1, child2)

                for mutant in offspring:
                    if random.random() < mutpb:
                        mutate(mutant, trucks)

                population[:] = offspring

            # Select the best individual
            best_ind = min(population, key=lambda ind: eval_truck_usage(ind))

            # Check for duplicate Truck IDs
            if not has_duplicates(best_ind):
                break

        # Store the output in an array
        output = [{"Truck ID": truck['id'], "Unique Number": truck['unique_number']} for truck in best_ind]

    # Print the output
    print("\nSelected trucks that can be assigned:\n")
    for truck in output:
        print(f"Truck ID: {truck['Truck ID']}, Unique Number: {truck['Unique Number']}")

if __name__ == "__main__":
    main()
