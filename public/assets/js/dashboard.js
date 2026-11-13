
document.addEventListener("DOMContentLoaded", function () {

    const canvas = document.getElementById("musicChart");

    if (!canvas) {
        return;
    }

    const ctx = canvas.getContext("2d");

    const gradient = ctx.createLinearGradient(
        0, 0, 0, 300
    );

    gradient.addColorStop(0, "rgba(255, 0, 204, 0.45)");
    gradient.addColorStop(1, "rgba(174, 0, 255, 0.02)");

    new Chart(ctx, {

        type: "line",

        data: {

            labels: musicLabels,

            datasets: [{

                label: "Reproduções",

                data: musicValues,

                borderColor: "#ff00cc",

                backgroundColor: gradient,

                borderWidth: 3,

                fill: true,

                tension: 0.4,

                pointBackgroundColor: "#ff5280",

                pointBorderColor: "#ffffff",

                pointRadius: 5,

                pointHoverRadius: 8

            }]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {

                    labels: {
                        color: "#ffffff"
                    }

                }

            },

            scales: {

                x: {

                    ticks: {
                        color: "#ffffff"
                    },

                    grid: {
                        color: "rgba(255,255,255,0.08)"
                    }

                },

                y: {

                    beginAtZero: true,

                    ticks: {
                        color: "#ffffff"
                    },

                    grid: {
                        color: "rgba(255,255,255,0.08)"
                    }

                }

            }

        }

    });

});
