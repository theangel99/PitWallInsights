document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById("telemetryChart").getContext("2d");
  
    fetch("openf1-proxy.php?driver_number=44&session_key=9693")
      .then((response) => response.json())
      .then((data) => {
        const timestamps = data.map((entry) => new Date(entry.date).toLocaleTimeString());
        const throttleData = data.map((entry) => entry.throttle * 100);
        const brakeData = data.map((entry) => (entry.brake ? 100 : 0));
  
        const telemetryChart = new Chart(ctx, {
          type: "line",
          data: {
            labels: timestamps,
            datasets: [
              {
                label: "Throttle (%)",
                data: throttleData,
                borderColor: "green",
                borderWidth: 1,
                fill: false,
              },
              {
                label: "Brake",
                data: brakeData,
                borderColor: "red",
                borderWidth: 1,
                fill: false,
                pointStyle: 'rect',
                pointRadius: 5,
              },
            ],
          },
          options: {
            responsive: true,
            scales: {
              x: {
                title: {
                  display: true,
                  text: "Time",
                },
              },
              y: {
                title: {
                  display: true,
                  text: "Intensity",
                },
                min: 0,
                max: 100,
              },
            },
          },
        });
      })
      .catch((error) => console.error("Error fetching telemetry data:", error));
  });
  