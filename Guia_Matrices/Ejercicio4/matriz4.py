Matriz = [
[4, 8, 3],
[9, 7, 5],
[8, 1, 4]
]

columnas = 3
numeroMayor = -99999

columna = int(input("Elegi una columna de la matriz (del 0 al 2): "))

while columna > 2:
    print ("elegi del 0 al 2")
    columna = int(input("Elegi una columna de la matriz (del 0 al 2): "))

while columna < 0:
    print ("elegi del 0 al 2")
    columna = int(input("Elegi columna de la matriz (del 0 al 2): "))

for i in range(columnas):
    for j in range(columnas): 
        if j == columna:
            if Matriz[i][j] > Mayor:
                Mayor = Matriz[i][j]

print(Mayor)
