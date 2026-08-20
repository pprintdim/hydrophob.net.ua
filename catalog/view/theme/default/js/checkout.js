// Получаем все элементы с классом custom-select
const customSelects = document.getElementsByClassName("custom-select");

for (let i = 0; i < customSelects.length; i++) {
  const selElmnt = customSelects[i].getElementsByClassName("select-selected")[0];
  const itemsElmnt = customSelects[i].getElementsByClassName("select-items")[0];

  // При клике на выбранный элемент
  selElmnt.addEventListener("click", function (e) {
    e.stopPropagation();
    closeAllSelect(this);
    itemsElmnt.classList.toggle("select-hide");
  });

  // При клике на опцию в списке
  const options = itemsElmnt.getElementsByTagName("div");
  for (let j = 0; j < options.length; j++) {
    options[j].addEventListener("click", function (e) {
      // Получаем иконку и текст из выбранной опции
      const iconSrc = this.querySelector(".select-icon").src;
      const text = this.textContent.trim();

      // Обновляем выбранный элемент
      selElmnt.innerHTML = `<img src="${iconSrc}" alt="${text}" class="select-icon" /> ${text}`;
      itemsElmnt.classList.add("select-hide");
    });
  }
}

// Закрываем все селекты, кроме текущего
function closeAllSelect(elmnt) {
  const items = document.getElementsByClassName("select-items");
  const selected = document.getElementsByClassName("select-selected");
  for (let i = 0; i < items.length; i++) {
    if (elmnt != selected[i]) {
      items[i].classList.add("select-hide");
    }
  }
}

// Закрываем селект при клике вне его
document.addEventListener("click", closeAllSelect); 


document.addEventListener('click', function(e) {
    if(e.target.matches('.busket__item-minus, .busket__item-plus')) {
        var btn = e.target;
        var item = btn.closest('.busket__item');
        var cart_id = item.dataset.cartId;
        var qtySpan = item.querySelector('.busket__item-count span');
        var qty = parseInt(qtySpan.textContent);

        qty = btn.classList.contains('busket__item-minus') ? Math.max(1, qty - 1) : qty + 1;

        var formData = new FormData();
        formData.append('quantity[' + cart_id + ']', qty);

        fetch('index.php?route=common/cart/updateQuantity', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(json => {
            if(json.success) {
                // Обновляем количество и цену товара
                qtySpan.textContent = json.products[cart_id].quantity;
                item.querySelector('.busket__item-price').textContent = json.products[cart_id].total;

                // Обновляем totals
json.totals.forEach(function(total, i) {
    var el = document.querySelector('#total_value_' + (i + 1));
    if(el) el.textContent = total.text;
});

                // Обновляем шапку корзины
                var headerBusket = document.querySelector('.header__busket span');
                if(headerBusket) headerBusket.textContent = json.text_items;
            }
        });
    }
});


