import mysql.connector
import random
import math

# Connect to the database
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="logistics_company"
)

cursor = db.cursor(dictionary=True)

# Fetch available trucks not currently delivering
def fetch_available_trucks():
    query = """
    SELECT t.id, t.unique_number, t.total_distance, t.truck_status
    FROM truck t
    LEFT JOIN truck_driver td ON t.id = td.id_truck
    LEFT JOIN schedule s ON td.id = s.id_schedule
    WHERE (s.status IS NULL OR s.status != 1) AND t.truck_status = 1
    """
    cursor.execute(query)
    return cursor.fetchall()

# Define the individual and population
def create_individual(trucks, min_trucks):
    return [random.choice(trucks) for _ in range(min_trucks)]

def create_population(trucks, best_trucks_count, n):
    return [create_individual(trucks, best_trucks_count) for _ in range(n)]

# Evaluate the fitness of an individual
def eval_truck_usage(individual):
    return sum(truck['total_distance'] for truck in individual),

# Check for duplicate Truck IDs
def has_duplicates(individual):
    truck_ids = [truck['id'] for truck in individual]
    return len(truck_ids) != len(set(truck_ids))

# Check for duplicate Truck IDs in an individual
def check_duplicates(individual):
    truck_ids = [truck['id'] for truck in individual]
    if len(truck_ids) != len(set(truck_ids)):
        return True
    return False

def print_available_trucks(trucks):
    jumlah_truk_tersedia = len(trucks)
    print("Truk yang tersedia:")
    
    # Buat set kosong untuk menyimpan ID truk yang sudah diprint
    printed_truck_ids = set()
    count = 0
    
    for truck in trucks:
        # Cek apakah ID truk sudah diprint sebelumnya
        if truck['id'] not in printed_truck_ids:
            print(f"Truck ID: {truck['id']}, Unique Number: {truck['unique_number']}")
            # Tambahkan ID truk ke dalam set printed_truck_ids
            printed_truck_ids.add(truck['id'])
            count+=1
    
    print('jumlah truk tersedia:', count)
    best_trucks_count = math.ceil(0.25 * count)  # Hitung jumlah truk terbaik
    print("Best trucks count (25% dari jumlah truk yang tersedia):", count)
    print("25% dari jumlah truk yang tersedia (bulat ke atas):", math.ceil(0.25 * count))
    return best_trucks_count
            
# Genetic Algorithm main loop with duplicate check
def main():
    trucks = fetch_available_trucks()
    
    # Print jumlah dan daftar truk yang tersedia
    best_trucks_count = print_available_trucks(trucks)
    
    # Mendefinisikan ukuran populasi dan generasi
    population_size = 10
    generations = 20
        
    if len(trucks) <= 3:
        print({"Truck ID": truck['id'], "Unique Number": truck['unique_number']} for truck in trucks)
    
    else:
        while True:
            # Membuat populasi awal dengan kombinasi 3 truk yang tersedia
            population = create_population(trucks, best_trucks_count, population_size)

            # Main loop genetik
            for gen in range(generations):
                # Evaluasi populasi
                fitnesses = list(map(eval_truck_usage, population))

                # Seleksi elitisme (ambil individu terbaik)
                best_inds = sorted(population, key=lambda ind: eval_truck_usage(ind))[:best_trucks_count]

                # Cek duplikasi Truck IDs
                if not any(check_duplicates(ind) for ind in best_inds):
                    # Cetak solusi terbaik
                    print("Best trucks:")
                    for i, ind in enumerate(best_inds):
                        print(f"Set {i+1}:")
                        for truck in ind:
                            print(f"Truck ID: {truck['id']}, Unique Number: {truck['unique_number']}")
                    break

            # Keluar dari loop jika tidak ada duplikasi
            if not any(check_duplicates(ind) for ind in best_inds):
                break

if __name__ == "__main__":
    main()
