Matriz = [
[4, 8, 3],
[9, 7, 5],
[8, 1, 4]
]

columnas = 3
suma = 0

for i in range(columnas):
    for j in range(columnas): 
        suma += Matriz[i][j] 

print("La suma de las matrices es ", suma)
