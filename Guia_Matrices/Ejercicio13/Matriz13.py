Matriz = [
[1, 2, 3],
[4, 5, 6],
[7, 8, 9]
]

columnas = 3

p = 0

for i in range(columnas):
    for j in range(columnas): 
            p = p +1
            if p == 1:
                numA = Matriz[i][j]
            if p == 2:
                numB = Matriz[i][j]
            if p == 3:
                numC = Matriz[i][j]
            if p == 4:
                numD = Matriz[i][j]
            if p == 5:
                numE = Matriz[i][j]
            if p == 6:
                numF = Matriz[i][j]
            if p == 7:
                numG = Matriz[i][j]
            if p == 8:
                numH = Matriz[i][j]
            if p == 9:
                numI = Matriz[i][j]


Matriz[0][0] = numI
Matriz[0][1] = numA
Matriz[0][2] = numB

Matriz[1][0] = numC
Matriz[1][1] = numD
Matriz[1][2] = numE

Matriz[2][0] = numF
Matriz[2][1] = numG
Matriz[2][2] = numH

print(Matriz)
