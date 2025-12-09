function frmLibros() {
    document.getElementById("frmLibro").reset();
    $("#id").val("");

    // Limpiar valores anteriores
    $('#autor').empty().append('<option value="">-- Seleccione Autor --</option>');
    $('#editorial').empty().append('<option value="">-- Seleccione Editorial --</option>');
    $('#materia').empty().append('<option value="">-- Seleccione Materia --</option>');

    // Cargar datos por AJAX
    fetch(base_url + "Autores/listarSelect")
        .then(response => response.json())
        .then(data => {
            data.forEach(row => {
                $('#autor').append(`<option value="${row.id}">${row.nombre}</option>`);
            });
        });

    fetch(base_url + "Editorial/listarSelect")
        .then(response => response.json())
        .then(data => {
            data.forEach(row => {
                $('#editorial').append(`<option value="${row.id}">${row.nombre}</option>`);
            });
        });

    fetch(base_url + "Materias/listarSelect")
        .then(response => response.json())
        .then(data => {
            data.forEach(row => {
                $('#materia').append(`<option value="${row.id}">${row.nombre}</option>`);
            });
        });

    $("#nuevoLibro").modal("show");
}
