class Product:
    def __init__(self, id, name, weight, length, width, height):
        self.id = id
        self.weight = weight
        self.name = name
        self.length = length
        self.width = width
        self.height = height
        
        self.volume = self.length * self.width * self.height

    def product_dest(self, dest):
        self.dest = dest


    def print_best_solution(best_solution):
        unique_elements = set()
        best_solutions = []

        for truck in best_solution:
            unique_sublist = []
            for item in truck:
                if item not in unique_elements:
                    unique_sublist.append(item)
                    unique_elements.add(item)
            best_solutions.append(unique_sublist)
        # print(best_solution)

        for i, truck in enumerate(best_solutions):
            print(f"Truck {i+1}:")
            for item in truck:
                print(f"  Product - ID: {item.id}, Name: {item.name},Weight: {item.weight}, Length: {item.length}, Width: {item.width}, Height: {item.height}, Destination: {item.dest.dest_city}")

class Destination:
    def __init__(self, distance):
        # self.fee = fee
        self.distance = distance
        # self.dest_city = dest_city