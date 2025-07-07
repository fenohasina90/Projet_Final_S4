function drawChart(labels, data) {
    const ctx = document.getElementById('interetChart').getContext('2d');

    if (chartInstance) {
        chartInstance.destroy(); // Détruire l'ancien graphique
    }

    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Intérêt Gagné (Ar)',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Montant (Ar)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Mois / Année'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Graphique des Intérêts Gagnés par Mois'
                }
            }
        }
    });
}
