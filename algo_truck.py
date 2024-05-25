import mysql.connector
from deap import base, creator, tools, algorithms
import random
import numpy
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
    SELECT DISTINCT t.id, t.unique_number, t.total_distance, t.truck_status
    FROM truck t
    LEFT JOIN truck_driver td ON t.id = td.id_truck
    LEFT JOIN schedule s ON td.id = s.id_schedule
    WHERE (s.status IS NULL OR s.status != 1) AND t.truck_status = 1
    """
    cursor.execute(query)
    return cursor.fetchall()

trucks = fetch_available_trucks()
min_trucks = max(3, math.ceil(len(trucks) * 0.25))

# Define the genetic algorithm functions
creator.create("FitnessMin", base.Fitness, weights=(-1.0,))
creator.create("Individual", list, fitness=creator.FitnessMin)

toolbox = base.Toolbox()
toolbox.register("attr_truck", random.choice, trucks)
toolbox.register("individual", tools.initRepeat, creator.Individual, toolbox.attr_truck, n=min_trucks)
toolbox.register("population", tools.initRepeat, list, toolbox.individual)

def evalTruckUsage(individual):
    # Calculate the total distance of trucks in the individual
    total_distance = sum(truck['total_distance'] for truck in individual)
    return (total_distance,)

toolbox.register("evaluate", evalTruckUsage)
toolbox.register("mate", tools.cxTwoPoint)
toolbox.register("mutate", tools.mutShuffleIndexes, indpb=0.05)
toolbox.register("select", tools.selTournament, tournsize=3)

# Initialize a set to store the IDs of selected trucks
selected_trucks = set()

# Define a custom mutation operator to prevent selecting the same truck twice
def mutate_truck(individual):
    for i in range(len(individual)):
        while True:
            # Fetch available trucks not currently delivering
            trucks = fetch_available_trucks()
            min_trucks = max(3, math.ceil(len(trucks) * 0.25))

            # Select a random truck from the available trucks
            truck = random.choice(trucks)
            if truck['id'] not in selected_trucks:
                individual[i] = truck
                selected_trucks.add(truck['id'])
                break

toolbox.register("mutate_truck", mutate_truck)

# Genetic Algorithm main loop
def main():
    if len(trucks) <= 3:
        output = [truck['id'] for truck in trucks]
        
    else:
        population = toolbox.population(n=50)
        hof = tools.HallOfFame(1, similar=lambda x, y: x == y)

        stats = tools.Statistics(lambda ind: ind.fitness.values)
        stats.register("avg", numpy.mean)
        stats.register("std", numpy.std)
        stats.register("min", numpy.min)
        stats.register("max", numpy.max)

        algorithms.eaSimple(population, toolbox, cxpb=0.5, mutpb=0.2, ngen=40, stats=stats, halloffame=hof, verbose=False)

        # Store the output in an array
        output = []
        while True:
            best_trucks = hof[0]
            truck_ids = [truck['id'] for truck in best_trucks]
            if len(set(truck_ids)) == len(truck_ids):  # Check for duplicate truck IDs
                output = [truck['id'] for truck in best_trucks]
                break
            else:
                # If there are duplicate truck IDs, re-run the genetic algorithm
                population = toolbox.population(n=50)
                hof = tools.HallOfFame(1, similar=lambda x, y: x == y)
                algorithms.eaSimple(population, toolbox, cxpb=0.5, mutpb=0.2, ngen=40, stats=stats, halloffame=hof, verbose=False)

    # Print the output
    # print("\nSelected trucks that can be assigned:\n")
    
    # print(output)
    return output

if __name__ == "__main__":
    main()