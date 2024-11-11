const ctx = document.getElementById('pie');

  new Chart(ctx, {
    type: 'pie',
    data: {
      labels: ['UI UX Designer', 'Web Designer', 'Freelancer', 'Client', 'Front end dev', 'Graphic Designer', 'Back end dev', 'Video Editor', 'Project Manager', 'Social Media Manager'],
      datasets: [{
        label: '#',
        data: [3, 0.5, 10, 5, 2, 3, 0.2, 5, 40, 40],
        backgroundColor: [
            'rgba(80, 180, 50, 1)',
            'rgba(100, 229, 114, 1)',
            'rgba(47, 126, 216, 1)',
            'rgba(13, 35, 58, 1)',
            'rgba(255, 150, 85, 1)',
            'rgba(237, 86, 27, 1)',
            'rgba(255, 242, 99, 1)',
            'rgba(221, 223, 0, 1)',
            'rgba(106, 249, 196, 1)',
            'rgba(36, 203, 229, 1)'
          ],
      }]
    },
    options: {
      responsiveness: true,
      maintainAspectRatio: false,  // Allow resizing
    }
  });