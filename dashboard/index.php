<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Clima</title>
  <link rel="stylesheet" href="cs.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
  <!-- Se incluye FontAwesome para los íconos -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    integrity="sha512-Fo3rlrZj/k7ujTTXRN/zywd2p5ROnUeE7Jua+ZBZ9zfnZkYl1jFWP+0e1YQjw5lExQX+eQ3C5FQ+VQKfYBzXmg=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
</head>
<body>
  <!-- Botón para mostrar/ocultar el menú lateral -->
  <button id="menuToggle" onclick="toggleMenu(event)"><i class="fas fa-bars"></i></button>
  
  <!-- Menú lateral -->
  <div id="sideMenu" class="side-menu">
    <h2>Menú</h2>
    <ul>
      <li><a onclick="showSection('inicio')">Inicio</a></li>
      <li><a onclick="showSection('alertas')">Alertas</a></li>
      <li><a onclick="showSection('pronostico')">Pronóstico</a></li>
      <li><a onclick="showSection('tendencias')">Tendencias</a></li>
    </ul>
  </div>
  
  <div class="dashboard">
    <!-- Sección: Inicio (Dashboard de sensores) -->
    <div id="section-inicio" class="section">
      <h1>Monitoreo Climático</h1>
      <div class="card">
        <h2>Seleccionar Sensor</h2>
        <div class="sensor-select-container">
          <select id="sensorSelect" onchange="mostrarInfo()">
            <option value="">Seleccione un sensor</option>
            <option value="Temperatura">Temperatura</option>
            <option value="Humedad">Humedad</option>
            <option value="Calidad del Aire">Calidad del Aire</option>
            <option value="Lluvia">Lluvia</option>
            <option value="Presión Atmosférica">Presión Atmosférica</option>
          </select>
        </div>
      </div>

      <!-- Mensaje de sensores instalados -->
      <div class="info-box" id="sensorCountBox">
        <p id="sensorCountText"></p>
      </div>
      
      <div class="content-container" id="infoPanel" style="display: none;">
        <!-- Información del sensor dentro de una card -->
        <div class="card info-container">
          <h2 id="sensorTitle"><i id="sensorIcon" class=""></i><span id="sensorName">Sensor</span></h2>
          <p id="sensorData">Datos en tiempo real</p>
          <p id="sensorStatus">Estado: <span style="color:green">Activo</span></p>
          <p id="sensorAvg">Promedio: --</p>
          <p id="sensorRecom">Recomendaciones: --</p>
          <button class="btn" onclick="toggleGraficas()">Cambiar Gráficas</button>
        </div>
        <div class="chart-container">
          <div class="chart-card">
            <canvas id="sensorChart"></canvas>
          </div>
          <!-- Gráfico secundario (oculto por defecto) -->
          <div class="chart-card hidden" id="extraChartContainer">
            <canvas id="sensorChart2"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Sección: Alertas -->
    <div id="section-alertas" class="section">
      <h1>Alertas</h1>
      <div class="card">
        <p>Simulación de alertas y envío de datos:</p>
        <!-- Lista de alertas se generará dinámicamente -->
        <ul id="alertas-list" class="alertas-list"></ul>
        <!-- Estado de las alertas -->
        <div id="alertas-status" class="alertas-status">
          <i class="fas fa-check-circle"></i> No hay alertas en este momento.
        </div>
        <div class="progress-container">
          <div class="progress-bar"></div>
        </div>
        <p class="alertas-status">Actualizando datos...</p>
      </div>
    </div>

    <!-- Sección: Pronóstico -->
    <div id="section-pronostico" class="section">
      <h1>Pronóstico</h1>
      <div class="card">
        <p id="locationName">Cargando ubicación...</p> <!-- Aquí aparecerá el nombre de la ciudad -->
        <p>Pronóstico en tiempo real para los próximos días:</p>
        <table>
          <thead>
            <tr>
              <th>Día</th>
              <th>Temperatura (API)</th>
              <th>Humedad (API)</th>
              <th>Temp (IoT)</th>
              <th>Humedad (IoT)</th>
            </tr>
          </thead>
          <tbody id="weatherTable">
            <!-- Datos se llenarán dinámicamente -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- Sección: Tendencias -->
    <div id="section-tendencias" class="section">
      <h1>Tendencias</h1>
      <div class="card">
        <p>Gráfica de tendencias históricas (simulada):</p>
        <div class="trend-container">
          <canvas id="trendChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Variables globales para los datos IoT obtenidos desde la BD
    let iotData = {};

    // Función para obtener datos reales del IoT desde la base de datos
    async function obtenerDatosIOT() {
      try {
        const response = await fetch("obtener_datos.php");
        const data = await response.json();
        const opcionesFecha = { weekday: "long", timeZone: "UTC" };
        let iot = {};
        data.forEach(item => {
          let fecha = new Date(item.fecha_registro);
          let dia = fecha.toLocaleDateString("es-ES", opcionesFecha).toLowerCase();
          iot[dia] = {
            temp: item.temperatura_suelo,
            humedad: item.humedad_suelo
          };
        });
        return iot;
      } catch (error) {
        console.error("Error al obtener datos IoT de la BD:", error);
        return {};
      }
    }

    // Función para obtener clima de la API y actualizar la tabla
    const apiKey = "d849db78e1be4a29c040242eaebf650e"; 
    const lat = "20.704543436586523"; // Cambia por tu latitud
    const lon = "-100.4434905827216"; // Cambia por tu longitud

    async function fetchWeather() {
      try {
        const response = await fetch(`https://api.openweathermap.org/data/2.5/forecast?lat=${lat}&lon=${lon}&units=metric&lang=es&appid=${apiKey}`);
        const data = await response.json();
        
        document.getElementById("locationName").innerText = `Ubicación: ${data.city.name}, ${data.city.country}`;
        await actualizarTabla(data);
      } catch (error) {
        console.error("Error obteniendo el clima:", error);
        document.getElementById("locationName").innerText = "No se pudo obtener la ubicación.";
      }
    }

    // Función para actualizar la tabla con datos de API e IoT reales
    async function actualizarTabla(data) {
      // Obtener los datos reales de la BD
      iotData = await obtenerDatosIOT();

      const tableBody = document.getElementById("weatherTable");
      tableBody.innerHTML = "";

      for (let i = 0; i < 4; i++) {
        let forecast = data.list[i * 8]; // Cada 8 registros es un día aproximado
        let fecha = new Date(forecast.dt_txt);
        let diaSemana = fecha.toLocaleDateString("es-ES", { weekday: "long", timeZone: "UTC" }).toLowerCase();

        let tempAPI = forecast.main.temp.toFixed(1);
        let humedadAPI = forecast.main.humidity;
        let tempIoT = iotData[diaSemana]?.temp || "-";
        let humedadIoT = iotData[diaSemana]?.humedad || "-";

        let row = `
          <tr>
            <td>${diaSemana.charAt(0).toUpperCase() + diaSemana.slice(1)}</td>
            <td>${tempAPI}°C</td>
            <td>${humedadAPI}%</td>
            <td>${tempIoT}°C</td>
            <td>${humedadIoT}%</td>
          </tr>
        `;
        tableBody.innerHTML += row;
      }
    }

    // Actualizar la API de clima cada 10 segundos
    fetchWeather();
    setInterval(fetchWeather, 10000);

    // --- Sección de Alertas (se mantiene la simulación para alertas) ---
    const umbrales = {
      temperatura: { max: 30 },  // Temperatura mayor a 30°C
      humedad: { min: 40 },      // Humedad menor a 40%
      calidadAire: { max: 150 }  // Calidad del aire (ppm) mayor a 150
    };

    function simularDatosAlertas() {
      return {
        temperatura: (20 + Math.random() * 25).toFixed(1),
        humedad: (30 + Math.random() * 40).toFixed(1),
        calidadAire: (100 + Math.random() * 100).toFixed(0)
      };
    }

    function verificarAlertas(data) {
      let alertas = [];
      if (parseFloat(data.temperatura) > umbrales.temperatura.max) {
        alertas.push(`⚠️ Temperatura alta: ${data.temperatura}°C`);
      }
      if (parseFloat(data.humedad) < umbrales.humedad.min) {
        alertas.push(`⚠️ Humedad baja: ${data.humedad}%`);
      }
      if (parseFloat(data.calidadAire) > umbrales.calidadAire.max) {
        alertas.push(`⚠️ Mala calidad del aire: ${data.calidadAire} ppm`);
      }
      return alertas;
    }

    function actualizarAlertas() {
      const datos = simularDatosAlertas();
      const alertas = verificarAlertas(datos);

      const alertasList = document.getElementById("alertas-list");
      const alertasStatus = document.getElementById("alertas-status");
      alertasList.innerHTML = "";

      if (alertas.length === 0) {
        alertasStatus.innerHTML = `<i class="fas fa-check-circle"></i> No hay alertas en este momento.`;
      } else {
        alertas.forEach(alerta => {
          const li = document.createElement("li");
          li.innerHTML = alerta;
          alertasList.appendChild(li);
        });
        alertasStatus.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Se han detectado alertas.`;

        fetch("jenviar_alerta.php", {
          method: "POST",
          body: JSON.stringify({ alertas }),
          headers: { "Content-Type": "application/json" }
        }).then(response => {
          console.log("Alerta enviada");
        }).catch(error => {
          console.error("Error al enviar la alerta:", error);
        });
      }
    }

    setInterval(actualizarAlertas, 10000);
    actualizarAlertas();

    // --- Sección de Sensores y Gráficas ---
    let sensorChart, sensorChart2;
    let sensorActual = "";
    let mostrarDosGraficas = false;
    // Inicialmente, en lugar de generar datos aleatorios, se asume que se obtendrán datos reales.
    // Aquí puedes definir 'datosTiempoReal' como un arreglo vacío o con datos iniciales reales.
    let datosTiempoReal = []; 
    let etiquetas = Array.from({ length: 10 }, (_, i) => i + 1);

    const recomendaciones = {
      "Temperatura": "Mantener entre 20°C y 30°C.",
      "Humedad": "Ideal entre 40% y 60%.",
      "Calidad del Aire": "Evitar exposición a niveles altos de contaminantes.",
      "Lluvia": "Precaución en exteriores con lluvia fuerte.",
      "Presión Atmosférica": "Valores normales entre 1010 y 1020 hPa."
    };

    const sensorCounts = {
      "Temperatura": 2,
      "Humedad": 2,
      "Calidad del Aire": 3,
      "Lluvia": 1,
      "Presión Atmosférica": 1
    };

    const sensorIcons = {
      "Temperatura": "fas fa-thermometer-half",
      "Humedad": "fas fa-tint",
      "Calidad del Aire": "fas fa-wind",
      "Lluvia": "fas fa-cloud-showers-heavy",
      "Presión Atmosférica": "fas fa-tachometer-alt"
    };

    function showSection(section) {
      const sections = ['inicio', 'alertas', 'pronostico', 'tendencias'];
      sections.forEach(s => {
        document.getElementById('section-' + s).style.display = 'none';
      });
      document.getElementById('section-' + section).style.display = 'block';
      if (section === 'tendencias' && !trendChart) {
        initTrendChart();
      }
      document.getElementById('sideMenu').classList.remove('open');
    }

    showSection('inicio');

    function mostrarInfo() {
      sensorActual = document.getElementById('sensorSelect').value;
      if (sensorActual) {
        document.getElementById('infoPanel').style.display = 'flex';
        document.getElementById('sensorName').innerText = sensorActual;
        document.getElementById('sensorIcon').className = sensorIcons[sensorActual];
        document.getElementById('sensorRecom').innerText = "Recomendaciones: " + recomendaciones[sensorActual];
        actualizarGrafica();
        document.getElementById('sensorCountText').innerHTML = `<strong>Total de Sensores Instalados:</strong> ${sensorCounts[sensorActual]}`;
        document.getElementById('sensorCountBox').style.display = 'block';
      } else {
        document.getElementById('infoPanel').style.display = 'none';
        document.getElementById('sensorCountBox').style.display = 'none';
      }
    }

    function actualizarGrafica() {
      if (sensorChart) sensorChart.destroy();
      let ctx = document.getElementById('sensorChart').getContext('2d');
      sensorChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: etiquetas,
          datasets: [{
            label: sensorActual,
            data: datosTiempoReal,
            backgroundColor: 'rgba(58, 125, 68, 0.3)',
            borderColor: 'rgba(58, 125, 68, 1)',
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      });

      if (mostrarDosGraficas) {
        if (sensorChart2) sensorChart2.destroy();
        let ctx2 = document.getElementById('sensorChart2').getContext('2d');
        sensorChart2 = new Chart(ctx2, {
          type: 'bar',
          data: {
            labels: etiquetas,
            datasets: [{
              label: sensorActual + " (Histórico)",
              data: datosTiempoReal,
              backgroundColor: 'rgba(93, 155, 84, 0.3)',
              borderColor: 'rgba(93, 155, 84, 1)',
              borderWidth: 2
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false
          }
        });
      }

      let promedio = (datosTiempoReal.reduce((a, b) => parseFloat(a) + parseFloat(b), 0) / (datosTiempoReal.length || 1)).toFixed(2);
      document.getElementById('sensorAvg').innerText = "Promedio: " + promedio;
    }

    function toggleGraficas() {
      mostrarDosGraficas = !mostrarDosGraficas;
      const extraChart = document.getElementById('extraChartContainer');
      if (mostrarDosGraficas) {
        extraChart.classList.remove('hidden');
        extraChart.classList.add('visible');
      } else {
        extraChart.classList.remove('visible');
        extraChart.classList.add('hidden');
      }
      actualizarGrafica();
    }

    function toggleMenu(event) {
      event.stopPropagation();
      const menu = document.getElementById('sideMenu');
      menu.classList.toggle('open');
    }

    document.addEventListener('click', function(event) {
      const menu = document.getElementById('sideMenu');
      const toggle = document.getElementById('menuToggle');
      if (!menu.contains(event.target) && !toggle.contains(event.target)) {
        menu.classList.remove('open');
      }
    });

    // --- Aquí eliminamos el setInterval que generaba datos aleatorios ---
    // En su lugar, se implementa una función para actualizar datos reales del sensor.
    async function actualizarDatosTiempoReal() {
      try {
        const response = await fetch("obtener_datos_sensor.php"); // Endpoint que debe devolver un arreglo de números reales
        const datos = await response.json();
        // Se asume que 'datos' es un arreglo con al menos 10 valores.
        datosTiempoReal = datos.slice(-10); // Toma los últimos 10 registros
        document.getElementById('sensorData').innerText = `Última lectura: ${datosTiempoReal[datosTiempoReal.length - 1]}`;
        actualizarGrafica();
      } catch (error) {
        console.error("Error al obtener datos reales del sensor:", error);
      }
    }

    // Llama a la función de actualización de datos reales cada 5 segundos.
    setInterval(() => {
      if (sensorActual) {
        actualizarDatosTiempoReal();
      }
    }, 5000);

    // --- Sección de Tendencias ---
    let trendChart;
    function initTrendChart() {
      const ctx = document.getElementById('trendChart').getContext('2d');
      trendChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4'],
          datasets: [{
            label: 'Tendencia de Temperatura',
            data: [25, 27, 30, 28],
            backgroundColor: 'rgba(58, 125, 68, 0.3)',
            borderColor: 'rgba(58, 125, 68, 1)',
            borderWidth: 2,
            fill: false
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      });
    }
  </script>
</body>
</html>
