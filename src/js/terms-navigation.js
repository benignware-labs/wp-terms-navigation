window.addEventListener('click', function(event) {
  console.log('click', event);

  const toggle = event.target.closest(`*[data-toggle='terms']`);

  if (toggle) {
    // const parentItem = event.target.closest(`.terms-item`);
    //
    // if (parentItem) {
    //   parentItem.classList.toggle('is-open');
    // }

    const menu = document.querySelector(toggle.getAttribute('data-target'));

    console.log('menu', menu);

    if (menu) {
      menu.classList.toggle('is-open');
    }
  }
});
