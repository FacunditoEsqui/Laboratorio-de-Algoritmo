Matriz = [
[8, 9, -2],
[12, 6, -5],
[17, 2, 10]
]

columnas = 3

Fila = int(input("Elegi fila de la matriz (del 0 al 2) para sumarla: "))

while Fila > 2:
    print ("elegi del 0 al 2")
    Fila = int(input("Elegi fila de la matriz (del 0 al 2): "))

while Fila < 0:
    print ("elegi del 0 al 2")
    Fila = int(input("Elegi fila de la matriz (del 0 al 2): "))

p = 0

for i in range(columnas):
    for j in range(columnas): 
        if i == Fila:
            p = p +1
            if p == 1:
                num1 = Matriz[i][j]
            if p == 2:
                num2 = Matriz[i][j]
            if p == 3:
                num3 = Matriz[i][j]

suma = num1 + num2 + num3

print("La suma de la fila es: ", suma)
