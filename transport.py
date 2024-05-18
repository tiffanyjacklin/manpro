class Transportation:
    def __init__(self, id, license, max_weight, fuel, km_liter, total_distance):
        self.id = id
        self.license = license
        self.max_weight = max_weight
        self.length = 560
        self.width = 220
        self.height = 200
        self.volume = self.length * self.width * self.height
        self.fuel = fuel
        self.km_liter = km_liter
        self.total_distance = total_distance
        self.weight = 0
        self.cost = 0
        self.used_volume = 0
        self.route = []
    
    def add_item(self,item):
        self.route.append(item)
        self.weight += item.weight

    def get_truckid(truck_list):
        temp = []
        for tr in truck_list:
            temp.append(tr.id)
        return temp

        
class Driver:
    def __init__(self, id, distance, experience):
        self.id_dr = id
        self.dist_dr = distance
        self.exp_dr = experience
        
