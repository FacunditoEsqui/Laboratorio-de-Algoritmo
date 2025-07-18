Matriz = [
[4, 5, -4],
[5, 2, -1],
[1, 0, 7]
]

columnas = 3

Fila1 = int(input("Elegi fila de la matriz (del 0 al 2) para intercambiar: "))

while Fila1 > 2:
    print ("elegi del 0 al 2")
    Fila1 = int(input("Elegi fila de la matriz (del 0 al 2) para intercambiar: "))

while Fila1 < 0:
    print ("elegi del 0 al 2")
    Fila1 = int(input("Elegi fila de la matriz (del 0 al 2) para intercambiar: "))


Fila2 = int(input("Elegi fila de la matriz (del 0 al 2) para intercambiar: "))

while Fila2 > 2:
    print ("elegi del 0 al 2")
    Fila2 = int(input("Elegi fila de la matriz (del 0 al 2) para intercambiar: "))

while Fila2 < 0:
    print ("elegi del 0 al 2")
    Fila2 = int(input("Elegi fila de la matriz (del 0 al 2) para intercambiar: "))


q = 0
p = 0

for i in range(columnas):
    for j in range(columnas): 
        if i == Fila1:
            p = p +1
            if p == 1:
                numA1 = Matriz[i][j]
            if p == 2:
                numA2 = Matriz[i][j]
            if p == 3:
                numA3 = Matriz[i][j]
        
        if i == Fila2:
            q = q +1
            if q == 1:
                numB1 = Matriz[i][j]
            if q == 2:
                numB2 = Matriz[i][j]
            if q == 3:
                numB3 = Matriz[i][j]

Matriz[Fila2] = [numA1, numA2, numA3]

Matriz[Fila1] = [numB1, numB2, numB3]

print("Asi quedo la matriz: ", Matriz)
