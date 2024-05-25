import math
import random
    
class SimulatedAnnealing:
    def init_solution(trucks, drivers):
        solution = []
        for truck in trucks:
            dr1, dr2 = random.sample(drivers,2)
            solution.append((truck.id, [dr1, dr2]))
        return solution
    
    def fitness(solution):
        fit = 0

        for _, truck in solution:
            dr1, dr2 = truck
            if dr1.exp_dr < 5 and dr2.exp_dr < 5:
                fit += 10000000
            fit += dr1.dist_dr + dr2.dist_dr

        return fit
    
    def neighbor(solution, trucks, drivers):
        new_sol = solution[:]
        truck_idx = random.randint(0, len(trucks) - 1)
        driver_idx = random.randint(0,1)

        used_dr = set(dr for _,drs in new_sol for dr in drs)
        remain_dr = [dr for dr in drivers if dr not in used_dr]

        if len(remain_dr) > 0:
            new_driver = random.choice(remain_dr)

            while new_driver in new_sol[truck_idx][1]:
                new_driver = random.choice(remain_dr)
            new_sol[truck_idx][1][driver_idx] = new_driver

        return new_sol
    
    def simul_ann(temperature, final_T, cool_rate, trucks, drivers):
        if len(drivers) < 2 * len(trucks):
            trucks = trucks[:(len(drivers) // 2)]

        solution = SimulatedAnnealing.init_solution(trucks, drivers)
        fitness = SimulatedAnnealing.fitness(solution)
        best_sol = solution[:]
        best_fitness = fitness

        while temperature > final_T:
            new_sol = SimulatedAnnealing.neighbor(solution, trucks, drivers)
            new_fitness = SimulatedAnnealing.fitness(new_sol)

            if new_fitness < fitness or random.uniform(0,1) < math.exp((fitness - new_fitness) / temperature):
                solution = new_sol
                fitness = new_fitness
                if fitness < best_fitness:
                    best_sol = solution[:]
                    best_fitness = fitness
            temperature *= cool_rate

        return best_sol
