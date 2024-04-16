class Transportation:
    def __init__(self, license, max_weight, fuel, km_liter):
        self.license = license
        self.max_weight = max_weight
        self.length = 560
        self.width = 220
        self.height = 200
        self.volume = self.length * self.width * self.height
        self.fuel = fuel
        self.km_liter = km_liter
        self.weight = 0
        self.cost = 0
        self.used_volume = 0
        self.route = []
    
    def add_item(self,item):
        self.route.append(item)
        self.weight += item.weight
        
