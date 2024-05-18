from transport import Transportation
from TruckAlgo import *
import mysql.connector


mydb = mysql.connector.connect(
  host="localhost",
  user="root",
  password="",
  database="logistics_company"
)


def fetch_data():
    # mycursor = mydb.cursor()
    mycursor = mydb.cursor( buffered=True , dictionary=True)

    sql_truck = """
    SELECT *
    FROM truck
    LEFT JOIN (
        SELECT id_truck
        FROM truck_driver
        JOIN schedule ON truck_driver.id = schedule.id_schedule
        WHERE status = 1
    ) AS truck_sch ON truck.id = truck_sch.id_truck
    WHERE truck_sch.id_truck IS NULL AND truck.truck_status = 1"""
    mycursor.execute(sql_truck)
    mytruck = mycursor.fetchall()
 
    truck_list = truck(mytruck)

    return truck_list

def truck(truck_list):
    truck_lists = []
    for truck in truck_list:
        truck_lists.append(Transportation(truck["id"], truck["unique_number"], truck["capacity_kg"], truck["fuel_capacity"], truck["km_per_liter"], truck["total_distance"]))
    return truck_lists

def generate_trucks():
    trucks = fetch_data()
    percentage = 0.25
    # selected_trucks = QuickSelect.select_trucks(trucks, percentage)
    selected_trucks = BucketSort.bucket_sort(trucks, percentage)
    return selected_trucks