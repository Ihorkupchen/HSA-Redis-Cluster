### **Redis Sentinel Setup**
The **Redis master** is dynamically retrieved from Sentinel to ensure fault tolerance and high availability (see `php/RedisConnection.php`). If the master node changes, the application reconnects automatically using Sentinel.

---

### **Cache Stampede Pattern Implementation**

The **Cache Stampede** pattern is implemented to prevent multiple processes from overwhelming the system by trying to update the cache simultaneously (see `php/Cache.php`). This is achieved using **Redis locks** to ensure that **only one process updates the cache**, while others either use the stale cache or wait until the data becomes available.

---

#### **How Cache Stampede Works**

- **Test:** Run two or more instances of the following command simultaneously:
  ```bash
  php get_article.php
  ```
- **Expected Behavior:** 
  - The **first process** fetches the data from the "database" (simulated) and stores it in the Redis cache.

<img width="465" alt="Screenshot 2024-10-23 at 02 40 26" src="https://github.com/user-attachments/assets/9cf342aa-32c5-4148-a549-c198fd0ab141">

  - Other processes **detect the lock** and wait until the first process updates the cache.

<img width="528" alt="Screenshot 2024-10-23 at 02 40 39" src="https://github.com/user-attachments/assets/4f736510-db6d-4f78-8f90-28d77a90662c">

  - Once the cache is populated, subsequent processes retrieve the data from Redis without querying the database again.

---

### **Eviction Strategies Overview**

The following **eviction strategies** were tested to manage Redis memory usage effectively:

1. **noeviction — No keys are evicted**
   - **Description:** When memory is full, no keys are removed, and Redis will reject new write operations.
   - **Configuration:**
     ```bash
     redis-cli config set maxmemory-policy noeviction
     ```
   - **Test:**
     ```bash
     for i in {1..10000}; do redis-cli set key$i value$i; done
     ```
   - **Expected Behavior:** 
     When memory is exhausted, Redis will stop accepting new keys and return the following error:
     ```
     (error) OOM command not allowed when used memory > 'maxmemory'.
     ```

2. **allkeys-lru — Least Recently Used (LRU) keys are evicted**
   - **Description:** Redis removes the least recently used keys when memory is full.
   - **Configuration:**
     ```bash
     redis-cli config set maxmemory-policy allkeys-lru
     ```
   - **Test:**
     ```bash
     for i in {1..10000}; do redis-cli set key$i value$i; done
     redis-cli get key1
     ```
   - **Expected Behavior:** 
     If `key1` was rarely accessed, it may be evicted to free up memory.

3. **volatile-lru — Least Recently Used (LRU) keys with TTL are evicted**
   - **Description:** Only keys with a TTL (time-to-live) are considered for eviction.
   - **Configuration:**
     ```bash
     redis-cli config set maxmemory-policy volatile-lru
     ```
   - **Test:**
     ```bash
     redis-cli set key1 value1 EX 30
     for i in {2..10000}; do redis-cli set key$i value$i; done
     redis-cli get key1
     ```
   - **Expected Behavior:** 
     If `key1` was the least accessed key, it may be evicted despite having a TTL.

4. **allkeys-random — Random keys are evicted**
   - **Description:** Redis randomly evicts any key when memory is full.
   - **Configuration:**
     ```bash
     redis-cli config set maxmemory-policy allkeys-random
     ```
   - **Test:**
     ```bash
     for i in {1..10000}; do redis-cli set key$i value$i; done
     redis-cli keys *
     ```
   - **Expected Behavior:** 
     Some random keys will be removed when the memory limit is reached.

5. **volatile-random — Random keys with TTL are evicted**
   - **Description:** Randomly evicts only keys that have a TTL.
   - **Configuration:**
     ```bash
     redis-cli config set maxmemory-policy volatile-random
     ```
   - **Test:**
     ```bash
     redis-cli set key1 value1 EX 30
     redis-cli set key2 value2 EX 30
     for i in {3..10000}; do redis-cli set key$i value$i; done
     redis-cli keys *
     ```
   - **Expected Behavior:** 
     Only keys with a TTL will be randomly evicted.

6. **volatile-ttl — Keys with the shortest TTL are evicted**
   - **Description:** Keys with the shortest remaining TTL are removed first.
   - **Configuration:**
     ```bash
     redis-cli config set maxmemory-policy volatile-ttl
     ```
   - **Test:**
     ```bash
     redis-cli set key1 value1 EX 10
     redis-cli set key2 value2 EX 20
     redis-cli set key3 value3 EX 30
     redis-cli keys *
     ```
   - **Expected Behavior:** 
     When memory is full, the key with the shortest TTL (e.g., `key1`) will be removed first.
