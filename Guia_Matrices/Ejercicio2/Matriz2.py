Matriz = [
[4, 8, 3],
[9, 7, 5],
[8, 1, 4]
]

columnas = 3
positivos = 0

for i in range(columnas):
    for j in range(columnas): 
        if Matriz[i][j] > 0:
            positivos = positivos +1

print("La matriz ", positivos, " positivos")
