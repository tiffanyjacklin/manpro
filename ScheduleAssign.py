from psooo import PSO
from produk import Product, Destination
from transport import Transportation
import algo_truck
import mysql.connector

def convert_to_truck_products_array(arr):
    truck_products_dict = {}
    for i, truck_idx in enumerate(arr):
        if truck_idx != -1:  
            if truck_idx not in truck_products_dict:
                truck_products_dict[truck_idx] = []
            truck_products_dict[truck_idx].append(products[i].id)  
    num_trucks = max(truck_products_dict.keys()) + 1 
    truck_products_array = [truck_products_dict.get(truck_idx, []) for truck_idx in range(num_trucks)]
    return truck_products_array

mydb = mysql.connector.connect(
  host="localhost",
  user="root",
  password="",
  database="logistics_company"
)

def fetch_data(trucks):
    # mycursor = mydb.cursor()
    mycursor = mydb.cursor( buffered=True , dictionary=True)
    sql_product = "SELECT * FROM item WHERE status = 0"
    mycursor.execute(sql_product)
    myproduct = mycursor.fetchall()

    sql_location = "SELECT * FROM country_map"
    mycursor.execute(sql_location)
    mylocation = mycursor.fetchall()

    sql_truck = "SELECT * FROM truck WHERE id IN ({})".format(', '.join(map(str, trucks)))
    mycursor.execute(sql_truck)
    mytruck = mycursor.fetchall()

    product_list = Product.product(myproduct, mylocation)
    truck_list = Transportation.truck(mytruck)

    return product_list, truck_list

trucks = algo_truck.main()
products, trucks = fetch_data(trucks)

sol = PSO.pso(trucks, products)

solution = []
for i in sol:
    solution.append(convert_to_truck_products_array(i))

solution.append(Transportation.get_truckid(trucks))
print(solution)
