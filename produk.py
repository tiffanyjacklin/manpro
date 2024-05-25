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

    def product(product_list, location_list):
        product_lists = []
        for products in product_list:
            for location in location_list:
                if products["id_location_from"] == location["id_location_from"] and products["id_location_to"] == location["id_location_to"]:
                    dist = float(location["distance_m"])

            produk = Product(products["id"], products["item_name"], products["weight_kg"], products["panjang"], products["lebar"], products["tinggi"])
            produk.product_dest(Destination(dist/1000))
            product_lists.append(produk)
        return product_lists        
    
    def longest_subarray_length(arr):
        max_length = 0
        for sub_array in arr:
            length = len(sub_array)
            if length > max_length:
                max_length = length
        return max_length

    def print_sol(best_solution):
        unique_elements = set()
        unique_sublist = [[] for _ in range(len(best_solution))]
        index = 0
        while index <= Product.longest_subarray_length(best_solution):
            for truck in best_solution:
                for item in truck:
                    if item not in unique_elements:
                        unique_elements.add(item)
                        unique_sublist[best_solution.index(truck)].append(item)
                        break
            index += 1
        
        sublist = []
        for _, truck in enumerate(unique_sublist): 
            temp = []
            for product in truck:
                temp.append(product.id)
            sublist.append(temp)
        return sublist
    
class Destination:
    def __init__(self, distance):
        # self.fee = fee
        self.distance = distance
        # self.dest_city = dest_city