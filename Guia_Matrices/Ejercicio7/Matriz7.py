Matriz = [
[4, 8, 3],
[9, 7, 5],
[8, 1, 4]
]

columnas = 3
Mayor = -99999

for i in range(columnas):
    for j in range(columnas): 
        if Matriz[i][j] > Mayor:
            Mayor = Matriz[i][j]
            posicion = i,j

print(posicion)
