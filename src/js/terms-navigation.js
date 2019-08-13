window.addEventListener('click', function(event) {
  console.log('click', event);

  const toggle = event.target.closest(`*[data-toggle='terms']`);

  if (toggle) {
    const parentItem = event.target.closest(`.terms-item`);

    if (parentItem) {
      parentItem.classList.toggle('is-open');
    }
  }
});
