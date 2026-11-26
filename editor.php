<?php
// 食品エディタ（食事入力・変更で共通利用）
// 使い方： $foodsForJs（食品データベース）と $initialRows（初期表示する食品）を
// 設定してから、フォームの中で require する。フォームの id は mealForm とすること。
?>
<label>食品を検索して追加</label>
<input type="text" id="foodSearch" placeholder="食品名で検索（例：納豆）">
<div id="candidates"></div>

<div id="rows"></div>

<button type="button" class="btn sub small" id="addManual">＋ 食品を手入力で追加</button>
<p class="small">手入力した食品は栄養価なしとして扱われます。</p>

<input type="hidden" name="items_json" id="itemsJson">
<p class="error" id="formError"></p>

<script>
var FOODS = <?= json_encode($foodsForJs, JSON_UNESCAPED_UNICODE) ?>;
var ZERO = {energy:0, protein:0, fat:0, carbs:0, fiber:0, calcium:0, iron:0, vit_a:0, vit_c:0, vit_d:0};
var rows = <?= json_encode($initialRows, JSON_UNESCAPED_UNICODE) ?>;

function esc(s) {
  return String(s).replace(/[&<>"']/g, function(c) {
    return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
  });
}

function renderRows() {
  var html = '';
  rows.forEach(function(r, i) {
    html += '<div class="item-row">';
    if (r.food_id === null) {
      html += '<span class="nm"><input type="text" placeholder="食品名を入力" value="' + esc(r.name) + '" oninput="rows[' + i + '].name=this.value"></span>';
    } else {
      html += '<span class="nm">' + esc(r.name) + ' <span class="small">（' + esc(r.unit) + '）</span></span>';
    }
    html += '<button type="button" class="qty-btn" onclick="changeQty(' + i + ',-1)">−</button>';
    html += '<span>× ' + r.qty + '</span>';
    html += '<button type="button" class="qty-btn" onclick="changeQty(' + i + ',1)">＋</button>';
    html += '<button type="button" class="btn small danger" onclick="removeRow(' + i + ')">削除</button>';
    html += '</div>';
  });
  document.getElementById('rows').innerHTML = html;
}

function changeQty(i, d) {
  rows[i].qty += d;
  if (rows[i].qty < 1) { rows[i].qty = 1; }
  renderRows();
}

function removeRow(i) {
  rows.splice(i, 1);
  renderRows();
}

document.getElementById('foodSearch').addEventListener('input', function() {
  var q = this.value.trim();
  var box = document.getElementById('candidates');
  if (q === '') { box.innerHTML = ''; return; }
  var html = '';
  var count = 0;
  FOODS.forEach(function(f) {
    if (f.name.indexOf(q) !== -1 && count < 8) {
      html += '<button type="button" class="candidate" onclick="addFood(' + f.food_id + ')">' + esc(f.name) + '（' + esc(f.unit) + '）を追加</button>';
      count++;
    }
  });
  box.innerHTML = html;
});

function addFood(foodId) {
  for (var i = 0; i < FOODS.length; i++) {
    if (FOODS[i].food_id === foodId) {
      rows.push({food_id: FOODS[i].food_id, name: FOODS[i].name, unit: FOODS[i].unit, qty: 1, nutrients: FOODS[i].nutrients});
      break;
    }
  }
  document.getElementById('foodSearch').value = '';
  document.getElementById('candidates').innerHTML = '';
  renderRows();
}

document.getElementById('addManual').addEventListener('click', function() {
  rows.push({food_id: null, name: '', unit: '', qty: 1, nutrients: ZERO});
  renderRows();
});

document.getElementById('mealForm').addEventListener('submit', function(e) {
  var err = document.getElementById('formError');
  if (rows.length === 0) {
    err.textContent = '食品を1つ以上追加してください。';
    e.preventDefault();
    return;
  }
  for (var i = 0; i < rows.length; i++) {
    if (rows[i].food_id === null && rows[i].name.trim() === '') {
      err.textContent = '手入力の食品名が空です。';
      e.preventDefault();
      return;
    }
  }
  err.textContent = '';
  document.getElementById('itemsJson').value = JSON.stringify(rows);
});

renderRows();
</script>
