from psooo import PSO
from produk import Product, Destination
from transport import Transportation
import TruckAssign
import mysql.connector

mydb = mysql.connector.connect(
  host="localhost",
  user="root",
  password="",
  database="logistics_company"
)

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

products = fetch_data()
trucks = TruckAssign.generate_trucks()

sol = PSO.pso(trucks, products)

solution = []
for i in sol:
    solution.append(convert_to_truck_products_array(i))

solution.append(Transportation.get_truckid(trucks))
print(solution)
