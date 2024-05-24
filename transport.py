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

        
class Driver:
    def __init__(self, id, distance, experience):
        self.id_dr = id
        self.dist_dr = distance
        self.exp_dr = experience

        
