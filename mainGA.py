from gaddd import GenAlgo
from produk import Product, Destination
from transport import Transportation
import TruckAssign
import random
import mysql.connector
import json

mydb = mysql.connector.connect(
  host="localhost",
  user="root",
  password="",
  database="logistics_company"
)

def get_truckid(truck_list):
    temp = []
    for tr in truck_list:
        temp.append(tr.id)
    return temp

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
    while index <= longest_subarray_length(best_solution):
        for truck in best_solution:
            for item in truck:
                if item not in unique_elements:
                    unique_elements.add(item)
                    unique_sublist[best_solution.index(truck)].append(item)
                    break
        index += 1
    
    sublist = []
    for i, truck in enumerate(unique_sublist): 
        temp = []
        # print(f"Truck {i+1}:")
        for product in truck:
            temp.append(product.id)
            # print(f"  Product - ID: {product.id}, Name: {product.name}, Weight: {product.weight}, Length: {product.length}, Width: {product.width}, Height: {product.height}, Destination: {product.dest.dest_city}, Jarak: {product.dest.distance}")
        sublist.append(temp)
    # print(sublist)
    return sublist

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

def fetch_data():
    # mycursor = mydb.cursor()
    mycursor = mydb.cursor( buffered=True , dictionary=True)
    sql_product = "SELECT * FROM item WHERE status = 0"
    mycursor.execute(sql_product)
    myproduct = mycursor.fetchall()

    sql_location = "SELECT * FROM country_map"
    mycursor.execute(sql_location)
    mylocation = mycursor.fetchall()

    product_list = product(myproduct, mylocation)

    return product_list




population_size = 10
mutation_rate = 0.1

product_list = fetch_data()
trucks = TruckAssign.generate_trucks()

population_size = 50
num_generations = 100
    
best_solution = GenAlgo.genetic_algorithm(population_size, num_generations, trucks, product_list)

result = []
for best in best_solution:
    result.append(print_sol(best.get_chrom()))
truck_id = Transportation.get_truckid(trucks)
result.append(truck_id)
print(result)
