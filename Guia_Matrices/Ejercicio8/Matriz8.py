Matriz = [
[8, 9, -2],
[12, 6, -5],
[17, 2, 10]
]

columnas = 3
elegido = int(input("Elegi un numero para buscar: "))

t = 0

for i in range(columnas):
    for j in range(columnas): 
        t = t + 1
        if Matriz[i][j] == elegido:
            posicion = (i, j)
            print("El numero esta en la posicion: ", posicion)

        elif t == 9:
            print("El numero no esta en la matriz ")

