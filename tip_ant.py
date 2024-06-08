import random as rn
import numpy as np
from numpy.random import choice as np_choice

class AntColony(object):

    def __init__(self, distances, locations, route, start, n_ants, n_best, n_iterations, decay, alpha=1, beta=1):
        self.distances  = distances
        self.all_inds = range(len(distances))
        self.pheromone = np.ones(self.distances.shape) / len(distances)
        self.id_to_index = {id_: index for index, id_ in enumerate(locations)}
        self.index_to_id = {index: id_ for index, id_ in enumerate(locations)}
        self.route = route
        self.start = self.id_to_index[start]
        self.n_ants = n_ants
        self.n_best = n_best
        self.n_iterations = n_iterations
        self.decay = decay
        self.alpha = alpha
        self.beta = beta

    def run(self): 
        shortest_path = None
        best_path = (None, np.inf)

        for i in range(self.n_iterations):
            all_paths = self.generate_all_paths()
            self.update_pheromone_trails(all_paths)
            if not all_paths:
                continue
            self.create_pheromone(all_paths, self.n_best, shortest_path=shortest_path)
            shortest_path = min(all_paths, key=lambda x: x[1])
            if shortest_path[1] < best_path[1]:
                best_path = shortest_path
            self.pheromone = self.pheromone * self.decay

        if best_path == (None, np.inf):
            return None

        return best_path[0]

    def generate_all_paths(self):
        all_paths = []
        written_paths = set()

        for i in range(self.n_ants):
            path = self.generate_path(self.start)
            path_str = str(path)
            if self.valid_path(path) and path_str not in written_paths:
                all_paths.append((path, self.generate_distance(path)))
                written_paths.add(path_str)
            elif path_str not in written_paths:
                written_paths.add(path_str)
            
        return all_paths

    def generate_path(self, start):
        path = [start]
        visited = set(path)
        prev = start
        for i in range(len(self.distances) - 1):
            move = self.move(self.pheromone[prev], self.distances[prev], visited)
            path.append(move)
            prev = move
            visited.add(move)
        return path

    def move(self, pheromone_row, distance_row, visited):
        pheromone = np.copy(pheromone_row)
        pheromone[list(visited)] = 0
        
        attractiveness = pheromone ** self.alpha * ((1.0 / distance_row) ** self.beta)
        total = np.sum(attractiveness)
        if total == 0:
            return random.choice(list(set(range(len(pheromone))) - visited))
        
        probabilities = attractiveness / total
        return np.random.choice(range(len(probabilities)), p=probabilities)

    def valid_path(self, path):
        count = 0
        for start, end in self.route:
            start_indices = [i for i, x in enumerate(path) if x == self.id_to_index[start]]
            end_index = path.index(self.id_to_index[end])
            
            path_ids = [self.index_to_id[index] for index in path]
            check_duplicate, first, second = self.find_duplicate_index(path_ids)
            end_index_other = None

            if check_duplicate:
                if end_index == first: 
                    end_index_other = second
                
            valid = False
            for index_start in start_indices:
                if index_start < end_index or (end_index == len(path) - 1 and index_start == 0):
                    valid = True
                    break
                elif end_index_other is not None:
                    if index_start < end_index_other or (end_index_other == len(path) - 1 and index_start == 0):
                        valid = True
                        break
            if valid:
                count += 1

        if count == len(self.route):
            return True
        else:
            return False
    
    def find_duplicate_index(self, arr):
        seen = {}
        for index, value in enumerate(arr):
            if value in seen:
                return True, seen[value], index
            seen[value] = index
        return False, None, None

    def generate_distance(self, path):
        distance = 0
        for i in range(len(path) - 1):
            distance += self.distances[path[i]][path[i + 1]]
        return distance
    
    def update_pheromone_trails(self, all_paths):
        self.pheromone *= self.decay
        sorted_paths = sorted(all_paths, key=lambda x: x[1])
        for path, dist in sorted_paths[:self.n_best]:
            for move in range(len(path) - 1):
                self.pheromone[path[move]][path[move + 1]] += 1.0 / self.distances[path[move]][path[move + 1]]

    def create_pheromone(self, all_paths, n_best, shortest_path):
        sort = sorted(all_paths, key=lambda x: x[1])

        pheromone_updates = {}

        for path, distance in sort[:n_best]:
            for i in range(len(path) - 1):
                if (path[i], path[i + 1]) in pheromone_updates:
                    pheromone_updates[(path[i], path[i + 1])] += 1.0 / distance
                else:
                    pheromone_updates[(path[i], path[i + 1])] = 1.0 / distance

        for (i, j), update in pheromone_updates.items():
            self.pheromone[i][j] += update