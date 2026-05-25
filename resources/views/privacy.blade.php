<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Política de Privacidad - BiometricIP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
        body { background: #f8fafc; color: #1e293b; }
        .navbar-brand img { height: 40px; object-fit: contain; }
        .policy-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            padding: 3rem 0 2rem;
            text-align: center;
        }
        .policy-header h1 { font-size: 2rem; font-weight: 700; }
        .policy-header p { opacity: 0.85; font-size: 1rem; }
        .policy-body { max-width: 860px; margin: 0 auto; padding: 2.5rem 1.5rem 4rem; }
        h2 { font-size: 1.2rem; font-weight: 700; color: #4f46e5; margin-top: 2rem; border-left: 4px solid #4f46e5; padding-left: 0.75rem; }
        h3 { font-size: 1rem; font-weight: 700; color: #334155; margin-top: 1.25rem; }
        p, li { font-size: 0.95rem; line-height: 1.75; color: #475569; }
        .badge-law { font-size: 0.75rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; margin-left: 8px; vertical-align: middle; }
        .badge-co { background: #fce7f3; color: #be185d; }
        .badge-us { background: #dbeafe; color: #1d4ed8; }
        .info-box { background: #f1f5f9; border-radius: 0.75rem; padding: 1.25rem 1.5rem; margin: 1rem 0; }
        .info-box strong { color: #1e293b; }
        table { font-size: 0.9rem; }
        footer { background: #1e293b; color: #94a3b8; text-align: center; padding: 1.5rem; font-size: 0.85rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="{{ asset('logos/logo.png') }}" alt="BiometricIP">
        </a>
        <div class="ms-auto">
            <a href="{{ route('admin.login.show') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-right-to-bracket me-1"></i> Acceder
            </a>
        </div>
    </div>
</nav>

<div class="policy-header">
    <div class="container">
        <h1><i class="fa-solid fa-shield-halved me-2"></i>Política de Privacidad</h1>
        <p>Última actualización: {{ \Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e Y') }}<br>
        Vigente para usuarios en Colombia y Estados Unidos</p>
    </div>
</div>

<div class="policy-body">

    {{-- 1. IDENTIFICACIÓN --}}
    <h2>1. Identificación del Responsable del Tratamiento</h2>
    <div class="info-box">
        <strong>Razón social:</strong> Innovasoftip SAS<br>
        <strong>Producto:</strong> BiometricIP – Sistema de Control de Asistencia Biométrico<br>
        <strong>Correo de contacto:</strong> gerencia@innovasoftip.com<br>
        <strong>Sitio web:</strong> https://innovasoftip.com
    </div>
    <p>
        En cumplimiento de la <strong>Ley 1581 de 2012</strong> y su Decreto Reglamentario <strong>1377 de 2013</strong>
        (Colombia), y de la <strong>California Consumer Privacy Act – CCPA</strong> (EE. UU.), Innovasoftip SAS
        establece la presente Política de Tratamiento de Datos Personales.
    </p>

    {{-- 2. DATOS QUE RECOPILAMOS --}}
    <h2>2. Datos Personales que Recopilamos</h2>
    <p>BiometricIP recopila los siguientes datos en el ejercicio de sus funciones como sistema de control de asistencia:</p>

    <table class="table table-bordered table-sm mt-2">
        <thead class="table-light">
            <tr>
                <th>Categoría</th>
                <th>Datos</th>
                <th>Finalidad</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Identificación</strong></td>
                <td>Nombre completo, número de documento, cargo, área</td>
                <td>Identificar al empleado en el sistema</td>
            </tr>
            <tr>
                <td><strong>Contacto</strong></td>
                <td>Correo electrónico, teléfono</td>
                <td>Notificaciones y comunicación interna</td>
            </tr>
            <tr>
                <td><strong>Biométricos</strong> <span class="badge-law badge-co">Datos sensibles – Ley 1581</span></td>
                <td>Fotografía de reconocimiento facial (evidencia de marcaje)</td>
                <td>Prevenir suplantación de identidad</td>
            </tr>
            <tr>
                <td><strong>Geolocalización</strong></td>
                <td>Coordenadas GPS al momento del marcaje</td>
                <td>Verificar presencia en sede autorizada (geocerca)</td>
            </tr>
            <tr>
                <td><strong>Asistencia</strong></td>
                <td>Fecha, hora de entrada/salida, sede</td>
                <td>Control de jornada laboral y reportes</td>
            </tr>
            <tr>
                <td><strong>Dispositivo</strong></td>
                <td>IP del dispositivo móvil</td>
                <td>Trazabilidad y seguridad</td>
            </tr>
        </tbody>
    </table>

    <h3>Datos biométricos y sensibles <span class="badge-law badge-co">Colombia</span></h3>
    <p>
        Conforme al artículo 6 de la Ley 1581 de 2012, los datos biométricos (fotografías para reconocimiento facial)
        son considerados <strong>datos sensibles</strong>. Su tratamiento solo se realiza con autorización expresa,
        libre, previa e informada del titular, y con las medidas de seguridad reforzadas que establece la norma.
    </p>

    {{-- 3. PRINCIPIOS --}}
    <h2>3. Principios que Rigen el Tratamiento <span class="badge-law badge-co">Ley 1581/2012</span></h2>
    <ul>
        <li><strong>Legalidad:</strong> El tratamiento obedece a las normas vigentes aplicables.</li>
        <li><strong>Finalidad:</strong> Los datos se usan exclusivamente para el control de asistencia laboral.</li>
        <li><strong>Libertad:</strong> El tratamiento solo se efectúa con autorización previa del titular.</li>
        <li><strong>Veracidad:</strong> La información debe ser exacta, completa y actualizada.</li>
        <li><strong>Transparencia:</strong> Se garantiza acceso a la información sobre el tratamiento.</li>
        <li><strong>Acceso y circulación restringida:</strong> Los datos no se divulgan a terceros sin autorización.</li>
        <li><strong>Seguridad:</strong> Se aplican medidas técnicas y organizacionales para proteger los datos.</li>
        <li><strong>Confidencialidad:</strong> Quienes tratan los datos están obligados a guardar reserva.</li>
    </ul>

    {{-- 4. FINALIDADES --}}
    <h2>4. Finalidades del Tratamiento</h2>
    <p>Los datos personales recopilados son utilizados para:</p>
    <ul>
        <li>Registrar y verificar la asistencia de los empleados mediante código QR dinámico y geocerca.</li>
        <li>Generar reportes de jornada laboral, tardanzas y ausentismo para la empresa contratante.</li>
        <li>Prevenir fraude en el registro de asistencia (suplantación de identidad).</li>
        <li>Cumplir obligaciones laborales y legales del empleador.</li>
        <li>Mejorar el funcionamiento de la plataforma (análisis estadístico anonimizado).</li>
    </ul>
    <p>Los datos <strong>no serán usados</strong> para publicidad, perfilamiento comercial ni transferidos a terceros con fines distintos al objeto del servicio.</p>

    {{-- 5. DERECHOS TITULARES CO --}}
    <h2>5. Derechos de los Titulares <span class="badge-law badge-co">Colombia – Ley 1581</span></h2>
    <p>En Colombia, como titular de sus datos personales, usted tiene derecho a:</p>
    <ul>
        <li><strong>Conocer</strong> los datos personales que tenemos sobre usted.</li>
        <li><strong>Actualizar y rectificar</strong> datos que sean inexactos o incompletos.</li>
        <li><strong>Solicitar prueba</strong> de la autorización otorgada.</li>
        <li><strong>Revocar la autorización</strong> y/o solicitar la supresión de sus datos, siempre que no exista obligación legal de conservarlos.</li>
        <li><strong>Acceder gratuitamente</strong> a sus datos personales al menos una vez al mes.</li>
        <li><strong>Presentar quejas</strong> ante la Superintendencia de Industria y Comercio (SIC) por infracciones a la ley.</li>
    </ul>
    <p>Para ejercer sus derechos, envíe su solicitud a: <strong>gerencia@innovasoftip.com</strong> con asunto "Derechos HABEAS DATA". Responderemos en un plazo máximo de <strong>15 días hábiles</strong>.</p>
    <a href="{{ route('data-deletion') }}" class="btn btn-outline-danger btn-sm mt-1 mb-2">
        <i class="fa-solid fa-trash-can me-1"></i> Solicitar eliminación de mis datos
    </a>

    {{-- 6. DERECHOS TITULARES US --}}
    <h2>6. Derechos de los Titulares <span class="badge-law badge-us">EE. UU. – CCPA / Privacy Laws</span></h2>
    <p>Para usuarios ubicados en Estados Unidos, en especial residentes de California (CCPA), usted tiene derecho a:</p>
    <ul>
        <li><strong>Saber</strong> qué datos personales recopilamos, usamos, divulgamos o vendemos.</li>
        <li><strong>Eliminar</strong> sus datos personales, con ciertas excepciones legales.</li>
        <li><strong>Opt-out</strong> (no aplicable): BiometricIP <strong>no vende</strong> datos personales a terceros.</li>
        <li><strong>No discriminación:</strong> No recibirá un trato diferenciado por ejercer sus derechos.</li>
        <li><strong>Corrección</strong> de datos inexactos (derecho incorporado por CPRA 2023).</li>
        <li><strong>Limitación de uso</strong> de datos sensibles, incluyendo geolocalización precisa y datos biométricos.</li>
    </ul>
    <p>Para ejercer sus derechos CCPA, envíe su solicitud a <strong>gerencia@innovasoftip.com</strong>. Responderemos dentro de los <strong>45 días</strong> establecidos por ley (prorrogables 45 días adicionales con aviso previo).</p>
    <a href="{{ route('data-deletion') }}" class="btn btn-outline-danger btn-sm mt-1 mb-2">
        <i class="fa-solid fa-trash-can me-1"></i> Submit a Data Deletion Request
    </a>

    {{-- 7. TRANSFERENCIA --}}
    <h2>7. Transferencia y Transmisión de Datos</h2>
    <p>
        Los datos personales son procesados en servidores ubicados en Colombia y/o en servicios en la nube con
        certificaciones de seguridad internacionales (ISO 27001 / SOC 2). Cualquier transferencia internacional
        se realiza cumpliendo los estándares del artículo 26 de la Ley 1581 de 2012 y las disposiciones aplicables
        de la CCPA para transferencias transfronterizas.
    </p>
    <p>BiometricIP <strong>no comparte, vende ni cede</strong> datos personales a terceros con fines comerciales.</p>

    {{-- 8. SEGURIDAD --}}
    <h2>8. Medidas de Seguridad</h2>
    <p>Implementamos las siguientes medidas técnicas y organizacionales para proteger sus datos:</p>
    <ul>
        <li>Cifrado de datos en tránsito mediante TLS/HTTPS.</li>
        <li>Almacenamiento en bases de datos con acceso restringido y autenticación reforzada.</li>
        <li>Control de acceso basado en roles (solo personal autorizado accede a los datos).</li>
        <li>Tokens de autenticación de corta duración (Bearer Token + Sanctum).</li>
        <li>Códigos QR dinámicos que rotan cada 30 segundos para evitar capturas fraudulentas.</li>
        <li>Registros de auditoría de accesos y cambios en el sistema.</li>
    </ul>

    {{-- 9. RETENCIÓN --}}
    <h2>9. Retención de Datos</h2>
    <p>
        Los datos de asistencia se conservan durante el tiempo que dure la relación laboral del empleado con la
        empresa contratante, más un período adicional de <strong>5 años</strong> en cumplimiento de obligaciones
        laborales y tributarias colombianas (Código Sustantivo del Trabajo y DIAN). Transcurrido este plazo,
        los datos serán eliminados o anonimizados de forma segura.
    </p>

    {{-- 10. MENORES --}}
    <h2>10. Menores de Edad</h2>
    <p>
        BiometricIP está diseñado exclusivamente para el control de asistencia de <strong>empleados mayores de 18 años</strong>.
        No recopilamos conscientemente datos de menores de edad. Si se detecta que se han ingresado datos de un menor
        sin autorización, procederemos a eliminarlos de inmediato.
    </p>
    <p>
        En cumplimiento de la <strong>COPPA</strong> (Children's Online Privacy Protection Act, EE. UU.), no recopilamos
        datos de menores de 13 años bajo ninguna circunstancia.
    </p>

    {{-- 11. COOKIES --}}
    <h2>11. Cookies y Tecnologías de Seguimiento</h2>
    <p>
        El panel de administración web utiliza <strong>cookies de sesión</strong> estrictamente necesarias para
        autenticar al usuario y mantener su sesión activa. No utilizamos cookies de seguimiento, publicidad
        ni análisis de comportamiento de terceros.
    </p>

    {{-- 12. AUTORIZACIÓN --}}
    <h2>12. Autorización del Titular <span class="badge-law badge-co">Ley 1581/2012</span></h2>
    <p>
        La autorización para el tratamiento de datos es otorgada por el empleado al momento de su registro en la
        plataforma por parte de la empresa contratante, a través de un mecanismo escrito que incluye la descripción
        clara de las finalidades del tratamiento. Esta autorización puede ser revocada en cualquier momento mediante
        solicitud escrita, salvo que exista obligación legal de conservar los datos.
    </p>

    {{-- 13. CAMBIOS --}}
    <h2>13. Cambios a esta Política</h2>
    <p>
        Nos reservamos el derecho de actualizar esta política cuando sea necesario. Las modificaciones serán
        publicadas en esta página con la fecha de actualización. Para cambios sustanciales que afecten sus derechos,
        notificaremos a los usuarios con al menos <strong>30 días de anticipación</strong> por correo electrónico
        o aviso en la plataforma.
    </p>

    {{-- 14. CONTACTO --}}
    <h2>14. Contacto y Reclamaciones</h2>
    <div class="info-box">
        <strong>Responsable de Protección de Datos:</strong> Innovasoftip SAS<br>
        <strong>Correo Colombia (HABEAS DATA):</strong> gerencia@innovasoftip.com<br>
        <strong>Correo EE. UU. (CCPA):</strong> gerencia@innovasoftip.com<br>
        <strong>Autoridad colombiana:</strong> Superintendencia de Industria y Comercio – <a href="https://www.sic.gov.co" target="_blank">www.sic.gov.co</a><br>
        <strong>Autoridad California:</strong> California Privacy Protection Agency – <a href="https://cppa.ca.gov" target="_blank">cppa.ca.gov</a>
    </div>

    <p class="mt-4 text-muted small">
        Esta política fue elaborada en cumplimiento de la <strong>Ley 1581 de 2012</strong>, el
        <strong>Decreto 1377 de 2013</strong>, la <strong>Circular Externa 002 de 2015 de la SIC</strong>
        (Colombia), la <strong>California Consumer Privacy Act (CCPA)</strong>, la
        <strong>California Privacy Rights Act (CPRA 2023)</strong> y la
        <strong>Children's Online Privacy Protection Act (COPPA)</strong> (Estados Unidos).
    </p>

</div>

<footer>
    &copy; {{ date('Y') }} Innovasoftip SAS &mdash; BiometricIP &mdash;
    <a href="{{ url('/privacy') }}" class="text-info">Política de Privacidad</a>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
