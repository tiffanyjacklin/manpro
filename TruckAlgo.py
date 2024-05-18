from transport import Transportation
import math
import random

class QuickSelect:
    def quickselect(trucks, left, right, k):
        if len(trucks) == 0:
            return []

        if left == right:
            return trucks[left].total_distance, trucks
        
        pivot = QuickSelect.partition(trucks, left, right)
        if k == pivot:
            return trucks[k].total_distance, trucks
        elif k < pivot:
            return QuickSelect.quickselect(trucks, left, pivot - 1, k)
        else:
            return QuickSelect.quickselect(trucks, pivot + 1, right, k)
    
    def partition(trucks, left, right):
        pivot = trucks[right].total_distance
        i = left
        for j in range(left, right):
            if trucks[j].total_distance <= pivot:
                trucks[i], trucks[j] = trucks[j], trucks[i]
                i+=1
        trucks[i], trucks[right] = trucks[right], trucks[i]
        return i
    
    def select_trucks(trucks, percentage):
        n = len(trucks)
        k = int(math.ceil(percentage * n))
        dist, sorted_trucks = QuickSelect.quickselect(trucks, 0, n-1, k)
        
        selected = [truck for truck in trucks if truck.total_distance <= dist]
        # Minimum 3
        if(len(selected) < 3):
            for t in sorted_trucks:
                if t not in selected:
                    selected.append(t)
                if(len(selected) >= 3): break

        return selected

class BucketSort:
    def bucket_sort(trucks, percentage):
        if len(trucks) == 0:
            return []
        
        bucket_count = len(trucks)
        max_dist = max(trucks, key=lambda x: x.total_distance).total_distance
        if max_dist == 0 :
            val = 1
        else :
            val = max_dist / len(trucks)

        buckets = [[] for _ in range(bucket_count)]

        for truck in trucks:
            index = int(truck.total_distance / val)
            if index == bucket_count:
                index -= 1
            buckets[index].append(truck)
        
        for bucket in buckets:
            BucketSort.insertion_sort(bucket)

        sorted_trucks = []
        for bucket in buckets:
            sorted_trucks.extend(bucket)
        
        k = max(3, int(math.ceil(percentage * len(trucks))))

        selected_trucks = sorted_trucks[:k]
    
        return selected_trucks

    def insertion_sort(bucket):
        for i in range(1, len(bucket)):
            key = bucket[i]
            j = i - 1

            while j >= 0 and key.total_distance  < bucket[j].total_distance :
                bucket[j+1] = bucket[j]
                j -= 1
            bucket[j+1] = key

