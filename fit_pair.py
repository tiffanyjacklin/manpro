class Pair:
    def __init__(self, chrom, fitness):
        self.chrom = chrom
        self.fitness = fitness

    def get_chrom(self):
        return self.chrom

    def get_fit(self):
        return self.fitness