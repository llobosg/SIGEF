<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mantención - SIGEF</title>

<link rel="stylesheet" href="../styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

<style>
.submodal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.5);
    justify-content:center;
    align-items:center;
    z-index:2000;
}
.submodal-content{
    background:#fff;
    padding:1.5rem;
    border-radius:6px;
    width:100%;
    max-width:500px;
}
.submodal-close{
    float:right;
    font-size:1.5rem;
    cursor:pointer;
}
</style>
</head>

<body>
<div class="container">

<div class="page-title">
    <h2><i class="fas fa-wrench"></i> Mantención de Vehículos</h2>
</div>

<!-- BUSQUEDA -->
<div class="card">
    <h3><i class="fas fa-search"></i> Seleccionar Vehículo</h3>
    <input type="text" id="busquedaVehiculo" placeholder="Buscar vehículo..." autocomplete="off">
    <div id="resultadosBusqueda"
         style="position:absolute;background:#fff;border:1px solid #ddd;width:100%;display:none;z-index:1000;"></div>
</div>

<!-- PANEL VEHICULO (SIEMPRE VISIBLE) -->
<div id="panelVehiculo" class="card">
    <h3><i class="fas fa-car"></i> Datos del Vehículo</h3>
    <div id="datosVehiculo">Seleccione un vehículo desde la búsqueda</div>

    <button id="btnAgregarMantencion" class="btn-save" style="margin-top:1rem">
        <i class="fas fa-plus"></i> Agregar Registro
    </button>
</div>

<!-- MANTENCIONES (SIEMPRE VISIBLE) -->
<div id="panelMantenciones" class="card">
    <h3><i class="fas fa-history"></i> Historial de Mantenciones</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Kilometraje</th>
                <th>Taller</th>
                <th>Costo</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody id="cuerpoMantenciones"></tbody>
    </table>
    <div id="totalCostos" style="text-align:right;font-weight:bold">Total: $0</div>
</div>

</div>

<!-- SUBMODAL -->
<div id="submodalMantencion" class="submodal">
<div class="submodal-content">
<span class="submodal-close" id="cerrarSubmodal">&times;</span>

<h3 id="tituloSubmodal">Registro de Mantención</h3>

<form id="formMantencion">
<input type="hidden" id="id_mantencion">
<input type="hidden" id="id_vehiculo">

<label>Fecha *</label>
<input type="date" id="fecha_mant" required>

<label>Tipo *</label>
<select id="tipo_mant" required>
<option value="Preventiva">Preventiva</option>
<option value="Correctiva">Correctiva</option>
<option value="Carga Petróleo">Carga Petróleo</option>
</select>

<label>Kilometraje</label>
<input type="number" id="kilometraje">

<label>Taller</label>
<input type="text" id="taller">

<label>Costo *</label>
<input type="number" id="costo" required>

<button type="submit" class="btn-save">Guardar</button>
<button type="button" id="btnCancelarSubmodal" class="btn-cancel">Cancelar</button>
</form>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
function notify(msg,type='info'){
    const colors={info:'#3498db',error:'#e74c3c',success:'#27ae60',warning:'#f39c12'};
    Toastify({text:msg,backgroundColor:colors[type],duration:3000}).showToast();
}
window.error = msg => notify(msg,'error');
window.success = msg => notify(msg,'success');

let vehiculoActual=null;
let mantenciones=[];

document.addEventListener('DOMContentLoaded',()=>{
    configurarBusqueda();
    document.getElementById('btnAgregarMantencion').onclick=()=>{
        if(!vehiculoActual){
            window.error('Seleccione un vehículo primero');
            return;
        }
        abrirSubmodal();
    };
    document.getElementById('cerrarSubmodal').onclick=cerrarSubmodal;
    document.getElementById('btnCancelarSubmodal').onclick=cerrarSubmodal;
    document.getElementById('formMantencion').onsubmit=guardarMantencion;
});

function configurarBusqueda(){
    const input=document.getElementById('busquedaVehiculo');
    const cont=document.getElementById('resultadosBusqueda');

    input.addEventListener('input',async()=>{
        cont.innerHTML='';
        if(input.value.length<2){cont.style.display='none';return;}

        try{
            const r=await fetch(`../api/get_vehiculos_busqueda.php?q=${encodeURIComponent(input.value)}`);
            const data=await r.json();

            data.forEach(v=>{
                const div=document.createElement('div');
                div.textContent=`${v.patente} - ${v.marca} ${v.modelo}`;
                div.style.padding='6px';
                div.style.cursor='pointer';
                div.onclick=()=>seleccionarVehiculo(v);
                cont.appendChild(div);
            });
            cont.style.display='block';
        }catch(err){
            console.error(err);
            window.error('Error en búsqueda');
        }
    });
}

function seleccionarVehiculo(v){
    vehiculoActual=v;
    document.getElementById('id_vehiculo').value=v.id_vehiculo;
    document.getElementById('datosVehiculo').innerHTML=`
        <b>${v.marca} ${v.modelo}</b><br>
        Patente: ${v.patente}
    `;
    cargarMantenciones(v.id_vehiculo);
    document.getElementById('resultadosBusqueda').style.display='none';
}

async function cargarMantenciones(id){
    try{
        const r=await fetch(`../api/get_mantenciones.php?id_vehiculo=${id}`);
        mantenciones=await r.json();
    }catch(err){
        mantenciones=[];
    }
    renderTabla();
}

function renderTabla(){
    const tbody=document.getElementById('cuerpoMantenciones');
    tbody.innerHTML='';
    let total=0;

    mantenciones.forEach(m=>{
        total+=parseFloat(m.costo||0);
        tbody.innerHTML+=`
        <tr>
            <td>${m.fecha_mant}</td>
            <td>${m.tipo_mant}</td>
            <td>${m.kilometraje||''}</td>
            <td>${m.taller||''}</td>
            <td>$${Number(m.costo).toLocaleString()}</td>
            <td>
                <button onclick="eliminarMantencion(${m.id_mantencion})">🗑</button>
            </td>
        </tr>`;
    });
    document.getElementById('totalCostos').textContent=`Total: $${total.toLocaleString()}`;
}

function abrirSubmodal(){
    document.getElementById('formMantencion').reset();
    document.getElementById('submodalMantencion').style.display='flex';
}
function cerrarSubmodal(){
    document.getElementById('submodalMantencion').style.display='none';
}

async function guardarMantencion(e){
    e.preventDefault();
    try{
        const data={
            id_vehiculo:vehiculoActual.id_vehiculo,
            fecha_mant:fecha_mant.value,
            tipo_mant:tipo_mant.value,
            kilometraje:kilometraje.value,
            taller:taller.value,
            costo:costo.value
        };
        const r=await fetch('../api/mantencion_logic.php',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify(data)
        });
        const res=await r.json();
        if(res.success){
            success(res.message||'Guardado');
            cerrarSubmodal();
            cargarMantenciones(vehiculoActual.id_vehiculo);
        }else{
            window.error(res.message||'Error');
        }
    }catch(err){
        window.error('Error al guardar');
    }
}

async function eliminarMantencion(id){
    if(!confirm('¿Eliminar registro?'))return;
    await fetch(`../api/mantencion_logic.php?id=${id}`,{method:'DELETE'});
    cargarMantenciones(vehiculoActual.id_vehiculo);
}
</script>
</body>
</html>
