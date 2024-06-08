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

def fetch_data():
    mycursor = mydb.cursor( buffered=True , dictionary=True)
    sql_id_schedules = "SELECT DISTINCT `schedule`.`id_schedule`, `truck`.`id_location` FROM `truck_driver` JOIN `truck` ON `truck_driver`.`id_truck` = `truck`.`id` JOIN `schedule` ON `schedule`.`id_schedule` = `truck_driver`.`id` WHERE `schedule`.`status` = 1 ORDER BY `schedule`.`id_schedule`"
    mycursor.execute(sql_id_schedules)
    id_schedules = mycursor.fetchall()

    all_schedule_details = []
    for schedule in id_schedules:
        id_schedule = schedule['id_schedule']
        sql_details = "SELECT `id_schedule`, `id_barang`, `id_location_from`, `id_location_dest` FROM `schedule` WHERE `id_schedule` = %s ORDER BY `id_schedule`"
        mycursor.execute(sql_details, (id_schedule,))
        schedule_details = mycursor.fetchall()
        all_schedule_details.append(schedule_details)

    sql_location = "SELECT `id_location_from`, `id_location_to`, `distance_m` FROM country_map"
    mycursor.execute(sql_location)
    mylocation = mycursor.fetchall()

    return id_schedules, all_schedule_details, mylocation

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

id_schedules, all_schedule_details, mylocation = fetch_data()
count = 0
shortest_paths = []
schedule_ids = []

for id_schedule in id_schedules:

    schedule_id = id_schedule['id_schedule']
    schedule_ids.append(schedule_id)
    location_id = id_schedule['id_location']
    schedule_details = all_schedule_details[count]
    route = [[item['id_location_from'], item['id_location_dest']] for item in schedule_details]
    
    locations = set()
    for detail in schedule_details:
        locations.add(detail['id_location_from'])
        locations.add(detail['id_location_dest'])
    locations = list(locations)
    if location_id in locations:
        locations.remove(location_id)
        locations.insert(0, location_id)
    else:
        locations.insert(0, location_id)

    location_id_found = any(item['id_location_dest'] == location_id for item in schedule_details)
    if location_id_found:
        locations.append(location_id)

    full_distance_matrix, loc_index = build_full_distance_matrix(mylocation)
    distance_matrix = filter_distance_matrix(full_distance_matrix, loc_index, locations)

    ant_colony = AntColony(distance_matrix, locations, route, location_id, 100, 5, 100, 0.95, alpha=1, beta=1)
    shortest_path = ant_colony.run()
   
    if shortest_path is not None:
        shortest_path_ids = [ant_colony.index_to_id[index] for index in shortest_path]

    shortest_paths.append(shortest_path_ids)
    count += 1

result = []
result.append(shortest_paths)
result.append(schedule_ids)
print(result)