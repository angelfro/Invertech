import mysql.connector
import numpy as np
import datetime
import joblib
from sklearn.linear_model import LinearRegression

# Conectar a la base de datos
conexion = mysql.connector.connect(
    host="localhost",
    user="root",
    password="Mipci2025@",
    database="clima_iot",
    ssl_disabled=True
)
cursor = conexion.cursor()

# Definir las variables de interés
variables = ["temperatura", "humedad", "calidad_aire", "humedad_suelo", "velocidad_aire", "lluvia", "temperatura_suelo"]

# Obtener datos históricos ordenados por fecha
cursor.execute("""
    SELECT fecha_registro, temperatura, humedad, calidad_aire, humedad_suelo, velocidad_aire, lluvia, temperatura_suelo
    FROM sensores ORDER BY fecha_registro ASC
""")
datos = cursor.fetchall()
conexion.close()

# Filtrar filas sin valores nulos
datos_limpios = [d for d in datos if all(d)]  # Filtra filas sin None ni NULL
if len(datos_limpios) < len(datos):
    print(f"⚠ Se eliminaron {len(datos) - len(datos_limpios)} filas con valores vacíos")

# Convertir datos limpios en arrays NumPy
fechas = np.array([d[0].timestamp() for d in datos_limpios]).reshape(-1, 1)

# Diccionario para almacenar modelos y predicciones
modelos = {}
predicciones = {var: [] for var in variables}

# Entrenar modelos de regresión para cada variable
for i, var in enumerate(variables):
    valores = np.array([d[i+1] for d in datos_limpios])  # +1 porque la fecha es el índice 0
    modelo = LinearRegression().fit(fechas, valores)
    modelos[var] = modelo

# Predecir para los próximos 5 días
dias_futuros = [datetime.datetime.now() + datetime.timedelta(days=i) for i in range(1, 6)]
fechas_pred = np.array([d.timestamp() for d in dias_futuros]).reshape(-1, 1)

for var in variables:
    predicciones[var] = modelos[var].predict(fechas_pred)

# Guardar predicciones en un archivo TXT
with open("predicciones.txt", "w") as file:
    file.write("Fecha," + ",".join(variables) + "\n")
    for i in range(5):
        valores_pred = [f"{predicciones[var][i]:.2f}" for var in variables]
        file.write(f"{dias_futuros[i].strftime('%Y-%m-%d')},{','.join(valores_pred)}\n")

print("✅ Predicciones guardadas en predicciones.txt")
