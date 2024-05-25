import sys
import bcrypt

# Function to hash a password
def hash_password(plain_password):
    # Generate a salt
    salt = bcrypt.gensalt()
    # Hash the password with the salt
    hashed_password = bcrypt.hashpw(plain_password.encode('utf-8'), salt)
    return hashed_password.decode('utf-8')  # Decode byte string to regular string

# Function to check a password
def check_password(plain_password, hashed_password):
    # Check if the provided password matches the hashed password
    return bcrypt.checkpw(plain_password.encode('utf-8'), hashed_password.encode('utf-8'))

if __name__ == "__main__":
    function_name = sys.argv[1]
    plain_password = sys.argv[2]

    if function_name == "hash_password":
        output = hash_password(plain_password)
    elif function_name == "check_password":
        hashed_password = sys.argv[3]
        output = str(check_password(plain_password, hashed_password))
    else:
        output = "Invalid function name"

    print(output)
