import mysql.connector
import numpy as np
import random
from tip_ant import AntColony

mydb = mysql.connector.connect(
  host="localhost",
  user="root",
  password="",
  database="logistics_company"
)

def fetch_data(truck_id, products):
    mycursor = mydb.cursor( buffered=True , dictionary=True)
    sql_truck_loc = "SELECT `truck`.`id`, `truck`.`id_location` FROM `truck` WHERE `id` = %s"
    mycursor.execute(sql_truck_loc, (truck_id, ))
    truck_loc = mycursor.fetchall()

    all_schedule_details = []
    for product in products:
        sql_details = "SELECT `id`, `id_location_from`, `id_location_to` FROM `item` WHERE `id` = %s"
        mycursor.execute(sql_details, (product,))
        schedule_details = mycursor.fetchall()
        all_schedule_details.append(schedule_details)

    return truck_loc, all_schedule_details

def fetch_my_location():
    mycursor = mydb.cursor( buffered=True , dictionary=True)
    sql_location = "SELECT `id_location_from`, `id_location_to`, `distance_m` FROM country_map"
    mycursor.execute(sql_location)
    mylocation = mycursor.fetchall()

    return mylocation

def build_full_distance_matrix(mylocation):
    locations = set()
    for dist in mylocation:
        locations.add(dist['id_location_from'])
        locations.add(dist['id_location_to'])
    
    loc_index = {loc: idx for idx, loc in enumerate(locations)}
    n = len(locations)
    distance_matrix = np.inf * np.ones((n, n))
    
    for dist in mylocation:
        i, j = loc_index[dist['id_location_from']], loc_index[dist['id_location_to']]
        distance_matrix[i, j] = dist['distance_m']
        distance_matrix[j, i] = dist['distance_m']
    
    return distance_matrix, loc_index

def filter_distance_matrix(full_distance_matrix, loc_index, relevant_locations):
    indices = [loc_index[loc] for loc in relevant_locations]
    n = len(indices)
    filtered_matrix = np.inf * np.ones((n, n))
    
    for i in range(n):
        for j in range(n):
            filtered_matrix[i, j] = full_distance_matrix[indices[i], indices[j]]
    
    return filtered_matrix

def main(schedules):
    truck_idx = schedules[3]
    mylocation = fetch_my_location()
    full_distance_matrix, loc_index = build_full_distance_matrix(mylocation)
    temp_result = []
    result = []
    
    for schedule in schedules[:3]:
        schedule_result = []
        # print("Schedule Index:", schedules.index(schedule))
        truck_result = []
        distance_result = []
        for truck in schedule:
            # print("Truck ID: ", truck_idx[schedule.index(truck)])
            truck_id = truck_idx[schedule.index(truck)]

            products = []
            for product in truck:
                products.append(product)

            # print("Products:", products)
            truck_loc, all_schedule_details = fetch_data(truck_id, products)

            # print("Truck Loc: ", truck_loc)
            location_id = truck_loc[0]['id_location']
            # print("Schedule Details: ", all_schedule_details)
            
            route = [[item['id_location_from'], item['id_location_to']] for items in all_schedule_details for item in items]
            # print(route)

            locations = set()
            for items in all_schedule_details:
                for detail in items:
                    locations.add(detail['id_location_from'])
                    locations.add(detail['id_location_to'])
            locations = list(locations)
            if location_id in locations:
                locations.remove(location_id)
                locations.insert(0, location_id)
            else:
                locations.insert(0, location_id)

            location_id_found = any(detail['id_location_to'] == location_id for items in all_schedule_details for detail in items)
            if location_id_found:
                locations.append(location_id)
            # print(locations)
            
            distance_matrix = filter_distance_matrix(full_distance_matrix, loc_index, locations)

            ant_colony = AntColony(distance_matrix, locations, route, location_id, 100, 10, 100, 0.95, alpha=1, beta=1)
            shortest_path, distance = ant_colony.run()
            # print(shortest_path)
            if shortest_path is not None:
                shortest_path_ids = [ant_colony.index_to_id[index] for index in shortest_path]
                truck_result.append(shortest_path_ids)
                distance_result.append(distance)
            # print("")
        temp_result.append(truck_result)
        temp_result.append(distance_result)
        result.append(temp_result)
        temp_result = []
    return result