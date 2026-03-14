// ═══════════════════════════════════════════════════════════════
// Archivo: inscripcion_rif_api.js (Sucesiones y API del Simulador)
// ═══════════════════════════════════════════════════════════════

window.pendingPayload = null; // Guardar payload temporalmente si se sobreescribirá

// Navegar via POST para que Chrome muestre "Confirmar reenvío" al recargar
function navigateViaPost(url) {
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    document.body.appendChild(form);
    form.submit();
}

function proceedWithOverwrite() {
    if (!window.pendingPayload || !window.simIntentoId) return;

    if (window.modalManager) {
        window.modalManager.close('overwriteConfirmDialog');
    }

    guardarBorradorBackend(window.pendingPayload);
}
window.confirmOverwriteAndProceed = proceedWithOverwrite;

function guardarBorradorBackend(payload) {
    if (!window.simIntentoId) {
        alert("Error: No se encontró un intento activo.");
        return;
    }

    var requestPayload = {
        borrador: payload,
        paso_actual: 0
    };

    var apiUrl = (window.simBaseUrl || '') + '/api/intentos/' + window.simIntentoId + '/guardar';

    fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(requestPayload)
    })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.ok) {
                var linkAnchor = document.getElementById('linkRegistrarDatos');
                if (linkAnchor && linkAnchor.getAttribute('data-url')) {
                    navigateViaPost(linkAnchor.getAttribute('data-url'));
                }
            } else {
                alert('Error al guardar el progreso en el servidor.');
            }
        })
        .catch(function (error) {
            console.error('Error:', error);
            alert('Ocurrió un error de red al intentar guardar.');
        });
}

// Normaliza fechas DD/MM/YYYY o YYYY-MM-DD a cadena canónica YYYYMMDD
function normalizeDateForComparison(dateStr) {
    if (!dateStr) return '';
    // YYYY-MM-DD (ISO)
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
        return dateStr.replace(/-/g, '');
    }
    // DD/MM/YYYY
    if (/^\d{2}\/\d{2}\/\d{4}$/.test(dateStr)) {
        var parts = dateStr.split('/');
        return parts[2] + parts[1] + parts[0];
    }
    return dateStr;
}

// Función personalizada para mostrar el mensaje con el link dinámico
function mostrarResultadoBusqueda(reincorporar) {
    // LLamamos primero a la validación nativa (existente en inscripcion_rif.js)
    if (typeof ValidarBusquedaPreinscritos === 'function' && !ValidarBusquedaPreinscritos(reincorporar)) {
        return false;
    }

    var selectedIndex = obtenerObjeto('personalidad', null).selectedIndex;
    var resultDiv = document.getElementById('searchResults');
    var linkAnchor = document.getElementById('linkRegistrarDatos');

    if (!resultDiv) return;

    if (selectedIndex !== 10 && selectedIndex !== 11) {
        resultDiv.style.display = 'block';
        if (linkAnchor) {
            linkAnchor.href = 'javascript:void(0);';
        }
        return;
    }

    var isConCedula = (selectedIndex === 10);

    var cedula = obtenerObjeto('cedulaPasaporte', 'value');
    var fecha = obtenerObjeto('fecha', 'value');
    var parroquia = obtenerObjeto('razonSocial', 'value');
    var numero_acta = obtenerObjeto('registroProvidencia', 'value');
    var year_acta = obtenerObjeto('tomoGaceta', 'value');

    var payload = {
        tipo_sucesion: isConCedula ? "Con_Cedula" : "Sin_Cedula",
        datos_basicos: {
            cedula: isConCedula ? cedula : null,
            fecha_fallecimiento: fecha,
            parroquia_acta: isConCedula ? null : parroquia,
            numero_acta: isConCedula ? null : numero_acta,
            year_acta: isConCedula ? null : year_acta
        }
    };

    // ── Si ya se generó el RIF Sucesoral ──
    if (window.simRifGenerado) {
        var rifMsgDiv = document.getElementById('rifExistsMessage');

        // Verificar si la búsqueda coincide con el borrador (datos que generaron el RIF)
        var matchesExisting = false;
        if (window.simBorrador && window.simBorrador.datos_basicos) {
            var db = window.simBorrador.datos_basicos;
            var jsonTipo = window.simBorrador.tipo_sucesion || '';
            var currentTipo = isConCedula ? "Con_Cedula" : "Sin_Cedula";

            if (jsonTipo === currentTipo) {
                if (isConCedula) {
                    if ((db.cedula || '') === cedula && normalizeDateForComparison(db.fecha_fallecimiento || '') === normalizeDateForComparison(fecha)) {
                        matchesExisting = true;
                    }
                } else {
                    if (normalizeDateForComparison(db.fecha_fallecimiento || '') === normalizeDateForComparison(fecha) &&
                        (db.parroquia_acta || '') === parroquia &&
                        (db.numero_acta || '') === numero_acta &&
                        (db.year_acta || '') === year_acta) {
                        matchesExisting = true;
                    }
                }
            }
        }

        if (matchesExisting) {
            // Coincide → mostrar mensaje rojo arriba del formulario
            resultDiv.style.display = 'none';
            if (rifMsgDiv) {
                rifMsgDiv.innerHTML =
                    '<span style="color:#FF0000; font-size:11px; font-family: Verdana, Arial; font-weight:bold;">' +
                        '"El contribuyente que intenta registrar ya existe, ' + window.simRifGenerado + ', ver Consulta de RIF."' +
                    '</span>';
                rifMsgDiv.style.display = 'block';
            }
        } else {
            // No coincide → mostrar link que abre modal informativo (abajo)
            if (rifMsgDiv) rifMsgDiv.style.display = 'none';
            resultDiv.style.display = 'block';
            resultDiv.innerHTML =
                '<div style="text-align:center;">' +
                    '<a href="javascript:void(0);" id="btnRifCompletado" style="color:#000080; text-decoration:none; font: 9px Verdana, Arial;">Inscribir contribuyente</a>' +
                '</div>';

            var btnCompletado = document.getElementById('btnRifCompletado');
            if (btnCompletado) {
                btnCompletado.onclick = function(e) {
                    e.preventDefault();
                    if (window.modalManager) {
                        window.modalManager.open('rifCompletadoModal');
                    } else {
                        alert('Usted ya completó el paso de inscripción de RIF Sucesoral. Su RIF asignado es: ' + window.simRifGenerado + '. Ahora debe dirigirse a los servicios de declaración sucesoral.');
                    }
                };
            }
        }
        return;
    }

    // ── Flujo normal (sin RIF generado) ──
    var hasDatosBasicos = false;
    var matchesBorrador = false;

    if (window.simBorrador && window.simBorrador.datos_basicos) {
        var db = window.simBorrador.datos_basicos;

        if (db.cedula || db.fecha_fallecimiento) {
            hasDatosBasicos = true;
        }

        var jsonTipo = window.simBorrador.tipo_sucesion || '';
        var currentTipo = isConCedula ? "Con_Cedula" : "Sin_Cedula";

        var jsonCedula = db.cedula || '';
        var jsonFecha = db.fecha_fallecimiento || '';
        var jsonParroquia = db.parroquia_acta || '';
        var jsonNumero = db.numero_acta || '';
        var jsonYear = db.year_acta || '';

        if (jsonTipo === currentTipo) {
            if (isConCedula) {
                if (jsonCedula === cedula && normalizeDateForComparison(jsonFecha) === normalizeDateForComparison(fecha)) {
                    matchesBorrador = true;
                }
            } else {
                if (normalizeDateForComparison(jsonFecha) === normalizeDateForComparison(fecha) &&
                    jsonParroquia === parroquia &&
                    jsonNumero === numero_acta &&
                    jsonYear === year_acta) {
                    matchesBorrador = true;
                }
            }
        }
    }

    resultDiv.style.display = 'block';

    var baseUrl = (window.simBaseUrl || '') + '/simulador/inscripcion-rif/datos-basicos';
    
    if (linkAnchor) {
        linkAnchor.setAttribute('data-url', baseUrl);
    }

    if (matchesBorrador) {
        // Construir el nombre desde el JSON guardado
        var db = window.simBorrador.datos_basicos;
        var hasName = db.apellidos || db.nombres;

        if (hasName) {
            var displayName = ((db.apellidos || '') + ', ' + (db.nombres || '')).toUpperCase();
            resultDiv.innerHTML =
                '<div style="text-align:center;">' +
                    '<a href="javascript:void(0);" onclick="navigateViaPost(\'' + baseUrl + '\')" style="color:#000080; text-decoration:none; font: 9px Verdana, Arial;">' + displayName + '</a><br><br>' +
                    '<span style="color:#000000; font: 9px Verdana, Arial;">1 registro(s) encontrado(s)</span>' +
                '</div>';
        } else {
            // Coincide pero no tiene nombre aún — resetear borrador con datos limpios y navegar
            resultDiv.innerHTML =
                '<div style="text-align:center;">' +
                    '<a href="javascript:void(0);" id="btnResetDraft" style="color:#000080; text-decoration:none; font: 9px Verdana, Arial;">Inscribir contribuyente</a>' +
                '</div>';

            var btnReset = document.getElementById('btnResetDraft');
            if (btnReset) {
                btnReset.onclick = function (e) {
                    e.preventDefault();
                    // Crear un link temporal para que guardarBorradorBackend pueda navegar
                    var tempLink = document.getElementById('linkRegistrarDatos');
                    if (!tempLink) {
                        tempLink = btnReset;
                        tempLink.id = 'linkRegistrarDatos';
                    }
                    tempLink.setAttribute('data-url', baseUrl);
                    guardarBorradorBackend(payload);
                };
            }
        }
    } else {
        // No hay datos: mostrar link para inscribir
        resultDiv.innerHTML =
            '<div style="text-align:center;">' +
                '<a href="javascript:void(0);" id="btnNewDraft" data-url="' + baseUrl + '" style="color:#000080; text-decoration:none; font: 9px Verdana, Arial;">Inscribir contribuyente</a>' +
            '</div>';

        var btnNewDraft = document.getElementById('btnNewDraft');
        var newLinkAnchor = document.getElementById('linkRegistrarDatos') || btnNewDraft;

        if (newLinkAnchor) {
            newLinkAnchor.id = 'linkRegistrarDatos';
            newLinkAnchor.onclick = function (e) {
                e.preventDefault();

                if (hasDatosBasicos) {
                    // Hay un borrador previo y es diferente a lo que acabamos de buscar
                    window.pendingPayload = payload;
                    if (window.modalManager) {
                        window.modalManager.open('overwriteConfirmDialog');
                    } else {
                        if (confirm("Ya tiene datos guardados diferentes a los que acaba de buscar. Si continúa, sobreescribirá el borrador y perderá su progreso. ¿Desea continuar?")) {
                            proceedWithOverwrite();
                        }
                    }
                } else {
                    // No hay borrador, guardar directamente la primera vez
                    guardarBorradorBackend(payload);
                }
            };
        }
    }
}
