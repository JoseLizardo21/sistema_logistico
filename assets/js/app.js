$(function () {
  // Sidebar toggle
  $('#sidebarToggle').on('click', function () {
    $('#sidebar').toggleClass('collapsed');
    $('.main-content').toggleClass('expanded');
  });

  // Init DataTables
  if ($.fn.DataTable) {
    $('.datatable').DataTable({
      language: {
        emptyTable:    'No hay registros para mostrar',
        zeroRecords:   'No se encontraron resultados',
        info:          'Mostrando _START_ a _END_ de _TOTAL_ registros',
        infoEmpty:     'Mostrando 0 a 0 de 0 registros',
        infoFiltered:  '(filtrado de _MAX_ registros totales)',
        search:        'Buscar:',
        paginate: {
          first:    'Primero',
          last:     'Último',
          next:     'Siguiente',
          previous: 'Anterior'
        },
        lengthMenu: 'Mostrar _MENU_ registros'
      },
      pageLength: 25,
      responsive: true,
      columnDefs: [{ targets: '_all', defaultContent: '' }]
    });
  }

  // Auto-dismiss alerts
  setTimeout(function () {
    $('.alert.fade.show').alert('close');
  }, 5000);
});

// Autocomplete helper used on forms
function setupMaterialAutocomplete(inputId, hiddenId, detalleId, unidadId, stockId) {
  var $inp   = $('#' + inputId);
  var $hid   = $('#' + hiddenId);
  var $det   = detalleId  ? $('#' + detalleId)  : null;
  var $uni   = unidadId   ? $('#' + unidadId)   : null;
  var $stk   = stockId    ? $('#' + stockId)    : null;
  var $drop  = $('<ul class="autocomplete-dropdown list-group position-absolute z-3 w-100"></ul>');

  $inp.after($drop).parent().css('position', 'relative');

  $inp.on('input', function () {
    var q = $(this).val().trim();
    if (q.length < 1) { $drop.hide(); return; }
    $.getJSON(BASE_URL + '/ajax/buscar_material.php', { q: q }, function (data) {
      $drop.empty().show();
      if (!data.length) { $drop.append('<li class="list-group-item text-muted small">Sin resultados</li>'); return; }
      data.forEach(function (m) {
        $('<li class="list-group-item list-group-item-action small py-1 px-2" style="cursor:pointer">' +
          '<b>' + m.codigo + '</b> — ' + m.detalle + ' <span class="text-muted">(' + m.unidad_medida + ')</span></li>')
          .on('click', function () {
            $inp.val(m.codigo);
            $hid.val(m.id);
            if ($det) $det.val(m.detalle);
            if ($uni) $uni.val(m.unidad_medida);
            if ($stk) $stk.val(m.stock_actual);
            $drop.hide();
          }).appendTo($drop);
      });
    });
  });

  $(document).on('click', function (e) {
    if (!$(e.target).closest($inp).length) $drop.hide();
  });
}

function setupAreaAutocomplete(inputId, hiddenId, respId) {
  var $inp   = $('#' + inputId);
  var $hid   = $('#' + hiddenId);
  var $resp  = respId ? $('#' + respId) : null;
  var $drop  = $('<ul class="autocomplete-dropdown list-group position-absolute z-3 w-100"></ul>');

  $inp.after($drop).parent().css('position', 'relative');

  $inp.on('input', function () {
    var q = $(this).val().trim();
    if (q.length < 1) { $drop.hide(); return; }
    $.getJSON(BASE_URL + '/ajax/get_area_info.php', { q: q }, function (data) {
      $drop.empty().show();
      if (!data.length) { $drop.append('<li class="list-group-item text-muted small">Sin resultados</li>'); return; }
      data.forEach(function (a) {
        $('<li class="list-group-item list-group-item-action small py-1 px-2" style="cursor:pointer">' +
          '<b>' + a.codigo + '</b> — ' + a.nombre + '</li>')
          .on('click', function () {
            $inp.val(a.codigo);
            $hid.val(a.id);
            if ($resp) {
              $.getJSON(BASE_URL + '/ajax/get_area_info.php', { area_id: a.id }, function (info) {
                if (info.responsable) $resp.val(info.responsable);
              });
            }
            $drop.hide();
          }).appendTo($drop);
      });
    });
  });

  $(document).on('click', function (e) {
    if (!$(e.target).closest($inp).length) $drop.hide();
  });
}

var BASE_URL = '/sis_logistico';
