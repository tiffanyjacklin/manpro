class Transportation:
    def __init__(self, id, license, max_weight, length, width, height, fuel, km_liter, total_distance):
        self.id = id
        self.license = license
        self.max_weight = max_weight
        self.length = length
        self.width = width
        self.height = height
        self.volume = self.length * self.width * self.height
        self.fuel = fuel
        self.km_liter = km_liter
        self.total_distance = total_distance
        self.weight = 0
        self.fit = 0
        self.used_volume = 0
        self.max_dest = (self.fuel - 20)*self.km_liter
        self.route = []

    def can_accommodate(self, item):
        return (self.used_volume + item.volume <= self.volume - 20 and
                self.weight + item.weight <= self.max_weight and
                item.dest.distance <= self.max_dest)
    
    def add_item(self,item):
        self.route.append(item)
        self.weight += item.weight
        self.used_volume += item.volume
        self.max_dest -= item.dest.distance
    
    def reset(self):
        self.route = []
        self.weight = 0
        self.used_volume = 0
        self.max_dest = (self.fuel - 20)*self.km_liter

    def get_truckid(truck_list):
        temp = [tr.id for tr in truck_list]
        return temp
    
    def truck(truck_list):
        truck_lists = []
        for truck in truck_list:
            truck_lists.append(Transportation(truck["id"], truck["unique_number"], truck["capacity_kg"], truck["panjang"], truck["lebar"], truck["tinggi"], truck["fuel_now"], truck["km_per_liter"], truck["total_distance"]))
        return truck_lists

        
class Driver:
    def __init__(self, id, distance, experience):
        self.id_dr = id
        self.dist_dr = distance
        self.exp_dr = experience
    
    def driver(dr_list):
        dr_lists = []
        for driver in dr_list:
            dr_lists.append(Driver(driver["id"], driver["total_distance"], driver["experience"]))
        return dr_lists

    def get_ids(solution):
        sol = []
        temp_tr = []
        for truck, drivers in solution:
            temp_tr.append(truck)
            if drivers[1].exp_dr > drivers[0].exp_dr:
                sol.append([drivers[1].id_dr, drivers[0].id_dr])
            else: 
                sol.append([drivers[0].id_dr, drivers[1].id_dr])
        sol.insert(0, temp_tr)
        return sol
        
