"use strict";

window.addEventListener('click', function (event) {
  console.log('click', event);
  var toggle = event.target.closest("*[data-toggle='terms']");

  if (toggle) {
    // const parentItem = event.target.closest(`.terms-item`);
    //
    // if (parentItem) {
    //   parentItem.classList.toggle('is-open');
    // }
    var menu = document.querySelector(toggle.getAttribute('data-target'));
    console.log('menu', menu);

    if (menu) {
      menu.classList.toggle('is-open');
    }
  }
});
