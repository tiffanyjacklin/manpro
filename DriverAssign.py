from transport import *
from DriverAlgo import *
import mysql.connector

def check_exp(solution):
    for _, truck in solution: 
      dr1, dr2 = truck
      if dr1.exp_dr < 5 and dr2.exp_dr < 5:
         return False
    return True
         

mydb = mysql.connector.connect(
  host="localhost",
  user="root",
  password="",
  database="logistics_company"
)

def fetch_data():
    # mycursor = mydb.cursor()
    mycursor = mydb.cursor( buffered=True , dictionary=True)

    sql_driver = """
    SELECT *
    FROM driver d
    LEFT JOIN (
        SELECT td.id_driver1 AS id_driver, s.status
        FROM truck_driver td
        JOIN schedule s ON td.id = s.id_schedule
        WHERE s.status = 1
        UNION
        SELECT td.id_driver2 AS id_driver, s.status
        FROM truck_driver td
        JOIN schedule s ON td.id = s.id_schedule
        WHERE s.status = 1
    ) AS truck_sch ON d.id = truck_sch.id_driver
    WHERE (truck_sch.id_driver IS NULL OR truck_sch.status != 1) AND d.driver_status = 1 
    """
    mycursor.execute(sql_driver)
    mydriver = mycursor.fetchall()

    sql_truck = """
    SELECT truck.* FROM truck
    JOIN truck_driver ON truck.id = truck_driver.id_truck
    WHERE truck_driver.id_driver1 IS NULL
    AND truck_driver.id_driver2 IS NULL;
    """
    # sql_truck = """
    # SELECT *
    # FROM truck
    # WHERE truck_status = 1
    # """
    mycursor.execute(sql_truck)
    mytruck = mycursor.fetchall()
 
    dr_list = Driver.driver(mydriver)
    tr_list = Transportation.truck(mytruck)

    return dr_list, tr_list

dr_lists, tr_lists = fetch_data()
T = 1000
final_T = 1
cool_rate = 0.99

check = False
while not check:
  solution = SimulatedAnnealing.simul_ann(T, final_T, cool_rate, tr_lists, dr_lists)
  check = check_exp(solution)

print(Driver.get_ids(solution))

