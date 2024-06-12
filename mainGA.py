from gaddd import GenAlgo
from produk import Product, Destination
from transport import Transportation
# import RouteAssign_fi_tif
import RouteAssign_tiffi
import algo_truck
import mysql.connector
import json

def main():
    mydb = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="logistics_company"
    )

    def get_best(solution): 
        best = []
        temp = []
        for pair in solution:
            if pair.get_chrom() not in temp:
                best.append(pair)
                temp.append(pair.get_chrom())
            if len(best) == 1:
                break
        return best

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
    product_lists, truck_lists = fetch_data(trucks)

    iteration = 0
    population_size = 50
    num_generations = 100
    best_solution = []

    check = []
    while len(best_solution) < 3 and iteration <=20:
        solution = (GenAlgo.genetic_algorithm(population_size, num_generations, truck_lists, product_lists))[:1]
        chrom = solution[0].get_chrom()
        if chrom not in check:
            best_solution.append(solution[0])
            check.append(chrom)
        iteration += 1

    result = []
    for bestt in best_solution:
        result.append(Product.print_sol(bestt.get_chrom()))
        
        
    # best_solution1 = []
    # best_solution2 = []
    # best_solution3 = []


    # # while len(best_solution) < 3:
    # while iteration <= 20:
    #     best_solution1 = GenAlgo.genetic_algorithm(population_size, num_generations, truck_lists, product_lists)
    #     best_solution1 = get_best(best_solution1)
    #     best_solution2 = GenAlgo.genetic_algorithm(population_size, num_generations, truck_lists, product_lists)
    #     best_solution2 = get_best(best_solution2)
    #     best_solution3 = GenAlgo.genetic_algorithm(population_size, num_generations, truck_lists, product_lists)
    #     best_solution3 = get_best(best_solution3)
    #     iteration += 1
    #     # print(iteration)
    #     # print(best_solution)


    # result = []
    # for bestt in best_solution1:
    #     result.append(Product.print_sol(bestt.get_chrom()))
    # for bestt in best_solution2:
    #     result.append(Product.print_sol(bestt.get_chrom()))
    # for bestt in best_solution3:
    #     result.append(Product.print_sol(bestt.get_chrom()))

    result.append(Transportation.get_truckid(truck_lists))
    routes = RouteAssign_tiffi.main(result)
    return json.dumps({
        "schedules": result,
        "routes": routes
    })

print(main())