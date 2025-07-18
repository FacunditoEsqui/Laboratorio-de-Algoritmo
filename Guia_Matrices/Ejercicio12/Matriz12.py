Matriz = [
[7, 4, -1],
[4, 7, -9],
[6, 1, 2]
]

columnas = 3

col1 = int(input("Elegi columna de la matriz (del 0 al 2) para intercambiar: "))

while col1 > 2:
    print ("elegi del 0 al 2")
    col1 = int(input("Elegi columna de la matriz (del 0 al 2) para intercambiar: "))

while col1 < 0:
    print ("elegi del 0 al 2")
    col1 = int(input("Elegi columna de la matriz (del 0 al 2) para intercambiar: "))


col2 = int(input("Elegi columna de la matriz (del 0 al 2) para intercambiar: "))

while col2 > 2:
    print ("elegi del 0 al 2")
    col2 = int(input("Elegi columna de la matriz (del 0 al 2) para intercambiar: "))

while col2 < 0:
    print ("elegi del 0 al 2")
    col2 = int(input("Elegi columna de la matriz (del 0 al 2) para intercambiar: "))


q = 0
p = 0

for i in range(columnas):
    for j in range(columnas): 
        if j == col1:
            p = p +1
            if p == 1:
                numA1 = Matriz[i][j]
            if p == 2:
                numA2 = Matriz[i][j]
            if p == 3:
                numA3 = Matriz[i][j]
        
        if j == col2:
            q = q +1
            if q == 1:
                numB1 = Matriz[i][j]
            if q == 2:
                numB2 = Matriz[i][j]
            if q == 3:
                numB3 = Matriz[i][j]

Matriz[0][col2] = numA1
Matriz[1][col2] = numA2
Matriz[2][col2] = numA3
Matriz[0][col1] = numB1
Matriz[1][col1] = numB2
Matriz[2][col1] = numB3

print("RESULTADO: ", Matriz)
