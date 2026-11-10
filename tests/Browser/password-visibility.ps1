param([Parameter(Mandatory = $true)][string]$HtmlUrl, [Parameter(Mandatory = $true)][string]$Browser, [ValidateSet('anggota', 'login', 'finance', 'manual', 'jenis', 'jenis-error', 'laporan', 'password-dom')][string]$Scenario = 'anggota')
$ErrorActionPreference = 'Stop'
$fixtureUri = [uri]$HtmlUrl
if ($fixtureUri.Scheme -ne 'http' -or $fixtureUri.Host -ne '127.0.0.1' -or $fixtureUri.AbsolutePath -ne '/fixture.html') { throw 'Only the isolated loopback fixture is allowed.' }
$browserRoot = Join-Path ([System.IO.Path]::GetTempPath()) ('kasorg-password-ui-' + [guid]::NewGuid().ToString('N'))
$null = New-Item -ItemType Directory -Path $browserRoot
$chromeProcess = $null
$socket = $null
$script:messageId = 0
$checks = [System.Collections.Generic.List[object]]::new()
function Invoke-Cdp([string]$Method, $Params = @{}) {
    $script:messageId++
    $id = $script:messageId
    $json = @{id = $id; method = $Method; params = $Params} | ConvertTo-Json -Depth 25 -Compress
    $bytes = [System.Text.Encoding]::UTF8.GetBytes($json)
    $segment = [System.ArraySegment[byte]]::new($bytes)
    $timeout = [System.Threading.CancellationTokenSource]::new(15000)
    try {
        $socket.SendAsync($segment, [System.Net.WebSockets.WebSocketMessageType]::Text, $true, $timeout.Token).GetAwaiter().GetResult()
        do {
            $stream = [System.IO.MemoryStream]::new()
            try {
                do {
                    $buffer = New-Object byte[] 65536
                    $received = $socket.ReceiveAsync([System.ArraySegment[byte]]::new($buffer), $timeout.Token).GetAwaiter().GetResult()
                    if ($received.MessageType -eq [System.Net.WebSockets.WebSocketMessageType]::Close) { throw 'Browser disconnected.' }
                    $stream.Write($buffer, 0, $received.Count)
                } until ($received.EndOfMessage)
                $reply = [System.Text.Encoding]::UTF8.GetString($stream.ToArray()) | ConvertFrom-Json
            } finally { $stream.Dispose() }
        } until ($reply.id -eq $id)
        if ($reply.error) { throw ('CDP: ' + $reply.error.message) }
        return $reply.result
    } finally { $timeout.Dispose() }
}
function Invoke-Js([string]$Expression) {
    $response = Invoke-Cdp 'Runtime.evaluate' @{expression = $Expression; returnByValue = $true; awaitPromise = $true}
    if ($response.exceptionDetails) { throw ('JavaScript: ' + $response.exceptionDetails.exception.description) }
    return $response.result.value
}
function Wait-Js([string]$Expression) {
    $deadline = [DateTime]::UtcNow.AddSeconds(4)
    do {
        if ((Invoke-Js $Expression) -eq $true) { return }
        if ([DateTime]::UtcNow -gt $deadline) { throw 'Expected browser state did not settle.' }
        Start-Sleep -Milliseconds 40
    } while ($true)
}
function Assert-Js([string]$Name, [string]$Expression) {
    # Bootstrap fade/focus transitions are asynchronous; do not mistake them for
    # Tailwind's synchronous class toggles. Keep the original assertion itself.
    $deadline = [DateTime]::UtcNow.AddSeconds(4)
    do {
        $ok = (Invoke-Js $Expression) -eq $true
        if ($ok -or [DateTime]::UtcNow -gt $deadline) { break }
        Start-Sleep -Milliseconds 40
    } while ($true)
    $checks.Add(@{name = $Name; passed = $ok})
    if (-not $ok) {
        $state = Invoke-Js "JSON.stringify({width:innerWidth,active:document.activeElement?.id,modals:[...document.querySelectorAll('.modal, [data-finance-modal]')].filter(m=>getComputedStyle(m).display!=='none').map(m=>({id:m.id,classes:m.className,opacity:getComputedStyle(m).opacity,transform:m.querySelector('.modal-dialog')?getComputedStyle(m.querySelector('.modal-dialog')).transform:null})),backdrops:document.querySelectorAll('.modal-backdrop').length})"
        throw ('FAIL: ' + $Name + ' | ' + $state)
    }
}
function Click-Element([string]$Selector) {
    $selectorJson = ConvertTo-Json $Selector -Compress
    # Bootstrap enables smooth scrolling. Measure only after an instant scroll,
    # otherwise a mobile click can hit the old coordinates during scrolling.
    $point = Invoke-Js "(async () => { const el = document.querySelector($selectorJson); if(!el)throw new Error('Click target missing'); el.scrollIntoView({block:'center',behavior:'instant'}); await new Promise(r=>requestAnimationFrame(()=>requestAnimationFrame(r))); const box = el.getBoundingClientRect(); if(box.width<=0||box.height<=0)throw new Error('Click target not visible'); return {x:box.x+box.width/2,y:box.y+box.height/2}; })()"
    $null = Invoke-Cdp 'Input.dispatchMouseEvent' @{type = 'mousePressed'; x = $point.x; y = $point.y; button = 'left'; clickCount = 1}
    $null = Invoke-Cdp 'Input.dispatchMouseEvent' @{type = 'mouseReleased'; x = $point.x; y = $point.y; button = 'left'; clickCount = 1}
}
function Press-Key([string]$Key, [string]$Code, [int]$VirtualKey) {
    $keyParams = @{type = 'keyDown'; key = $Key; code = $Code; windowsVirtualKeyCode = $VirtualKey; nativeVirtualKeyCode = $VirtualKey}
    if ($Key -eq 'Enter') { $keyParams.text = "`r"; $keyParams.unmodifiedText = "`r" }
    if ($Code -eq 'Space') { $keyParams.text = ' '; $keyParams.unmodifiedText = ' ' }
    $null = Invoke-Cdp 'Input.dispatchKeyEvent' $keyParams
    $null = Invoke-Cdp 'Input.dispatchKeyEvent' @{type = 'keyUp'; key = $Key; code = $Code; windowsVirtualKeyCode = $VirtualKey}
}
try {
    $chromeProcess = Start-Process -FilePath $Browser -ArgumentList @('--headless', '--disable-gpu', '--no-first-run', '--no-default-browser-check', '--remote-debugging-address=127.0.0.1', '--remote-debugging-port=0', ('--user-data-dir="' + $browserRoot + '"'), 'about:blank') -PassThru -WindowStyle Hidden -RedirectStandardError (Join-Path $browserRoot 'chrome.stderr.log') -RedirectStandardOutput (Join-Path $browserRoot 'chrome.stdout.log')
    $portFile = Join-Path $browserRoot 'DevToolsActivePort'
    $deadline = [DateTime]::UtcNow.AddSeconds(15)
    $port = $null
    while (-not $port) {
        if ([DateTime]::UtcNow -gt $deadline) { throw 'Chrome did not start.' }
        if (Test-Path -LiteralPath $portFile) {
            try {
                $portLines = @(Get-Content -LiteralPath $portFile)
                if ($portLines.Count -ge 2 -and $portLines[0] -match '^\d+$') { $port = $portLines[0] }
            } catch [System.IO.IOException] { }
        }
        if (-not $port) { Start-Sleep -Milliseconds 100 }
    }
    $pages = Invoke-RestMethod -Uri "http://127.0.0.1:$port/json" -TimeoutSec 5
    $page = $pages | Where-Object type -eq 'page' | Select-Object -First 1
    $socket = [System.Net.WebSockets.ClientWebSocket]::new()
    $null = $socket.ConnectAsync([uri]$page.webSocketDebuggerUrl, [System.Threading.CancellationToken]::None).GetAwaiter().GetResult()
    $null = Invoke-Cdp 'Page.enable'
    $null = Invoke-Cdp 'Emulation.setDeviceMetricsOverride' @{width = 1366; height = 900; deviceScaleFactor = 1; mobile = $false}
    $null = Invoke-Cdp 'Page.navigate' @{url = $fixtureUri.AbsoluteUri}
    $deadline = [DateTime]::UtcNow.AddSeconds(45)
    do {
        if ($Scenario -eq 'password-dom') {
            $ready = Invoke-Js "document.readyState === 'complete' && !!document.querySelector('#kasorg-browser-results')"
        } elseif ($Scenario -in @('finance', 'manual', 'jenis', 'jenis-error', 'laporan')) {
            $ready = Invoke-Js "document.readyState === 'complete' && !!window.lucide && !!document.querySelector('main table') && !!document.querySelector('aside svg') && getComputedStyle(document.querySelector('main')).paddingTop !== '0px'"
        } elseif ($Scenario -eq 'login') {
            $ready = Invoke-Js "document.readyState === 'complete' && !!window.lucide && !!window.bootstrap?.Modal && !!document.querySelector('#toggle-password svg') && document.querySelector('main').classList.contains('row') && getComputedStyle(document.querySelector('main')).display === 'flex'"
        } else {
            $ready = Invoke-Js "document.readyState === 'complete' && !!window.lucide && !!document.querySelector('[data-password-toggle] svg') && getComputedStyle(document.querySelector('#modal-anggota')).position === 'fixed'"
        }
        if ($ready) { break }
        if ([DateTime]::UtcNow -gt $deadline) { throw 'Lucide/CSS did not load in browser.' }
        Start-Sleep -Milliseconds 100
    } while ($true)
    if ($Scenario -eq 'password-dom') {
        $result = Invoke-Js "JSON.parse(document.querySelector('#kasorg-browser-results').textContent)"
        $result | ConvertTo-Json -Depth 10 -Compress
        return
    }
    $null = Invoke-Js @'
window.kasorgTest = {
    bootstrap: document.body.classList.contains('kasorg-bootstrap'),
    modalOpen: m => m.classList.contains('modal')
        ? m.classList.contains('show') && m.getAttribute('aria-modal') === 'true' && getComputedStyle(m).display === 'block'
            && getComputedStyle(m).opacity === '1' && getComputedStyle(m.querySelector('.modal-dialog')).transform === 'none'
        : m.classList.contains('flex') && !m.classList.contains('hidden') && getComputedStyle(m).display === 'flex',
    modalClosed: m => m.classList.contains('modal')
        ? !m.classList.contains('show') && m.getAttribute('aria-hidden') === 'true' && getComputedStyle(m).display === 'none'
            && !document.body.classList.contains('modal-open') && !document.querySelector('.modal-backdrop')
        : m.classList.contains('hidden') && getComputedStyle(m).display === 'none',
    financeModals: () => [...document.querySelectorAll('[data-bootstrap-finance-modal], [data-finance-modal]')],
    responsiveTable: () => document.querySelector('main table').closest(document.body.classList.contains('kasorg-bootstrap') ? '.table-responsive' : '.overflow-x-auto') !== null,
    focusVisible: el => {
        const style = getComputedStyle(el);
        const outline = style.outlineStyle !== 'none' && parseFloat(style.outlineWidth) >= 1 && style.outlineColor !== 'rgba(0, 0, 0, 0)';
        return document.activeElement === el && el.matches(':focus-visible') && (outline || style.boxShadow !== 'none');
    }
};
'@
    if ($Scenario -eq 'laporan') {
        $null = Invoke-Js "document.fonts.ready.then(()=>true)"
        foreach ($viewport in @(@{width=1440;height=900}, @{width=1366;height=768}, @{width=390;height=844})) {
            $width = $viewport.width
            $null = Invoke-Cdp 'Emulation.setDeviceMetricsOverride' @{width=$width; height=$viewport.height; deviceScaleFactor=1; mobile=($width -eq 390)}
            # Hilangkan hover sisa klik viewport sebelumnya sebelum memeriksa warna dasar tombol.
            $null = Invoke-Cdp 'Input.dispatchMouseEvent' @{type='mouseMoved';x=0;y=0}
            $null = Invoke-Js "new Promise(resolve=>setTimeout(()=>resolve(true),300))"
            Assert-Js "Laporan $width`: sidebar dan font Refan termuat" "getComputedStyle(document.querySelector('aside')).backgroundColor==='rgb(23, 37, 84)' && getComputedStyle(document.body).fontFamily.includes('Plus Jakarta Sans') && document.fonts.check('12px '+JSON.stringify('Plus Jakarta Sans'))"
            Assert-Js "Laporan $width`: empat kartu memakai nominal asli" "document.querySelectorAll('[data-stat]').length===4 && document.querySelector('[data-stat=awal]').dataset.amount==='0.00' && document.querySelector('[data-stat=masuk]').dataset.amount==='100.25' && document.querySelector('[data-stat=keluar]').dataset.amount==='30.10' && document.querySelector('[data-stat=akhir]').dataset.amount==='70.15'"
            Assert-Js "Laporan $width`: tabel lima kolom dan pembungkus responsif" "document.querySelectorAll('main table thead th').length===5 && document.querySelectorAll('main table tbody tr').length===2 && !!document.querySelector('main table').closest('.overflow-x-auto')"
            Assert-Js "Laporan $width`: dua garis dan titik grafik valid" "document.querySelectorAll('#grafik-laporan polyline').length===2 && [...document.querySelectorAll('#grafik-laporan circle')].every(c=>Number.isFinite(+c.getAttribute('cy')) && +c.getAttribute('cy')>=20 && +c.getAttribute('cy')<=130) && document.querySelector('#grafik-laporan [data-income]').dataset.income==='100.25' && document.querySelector('#grafik-laporan [data-expense]').dataset.expense==='30.10'"
            Assert-Js "Laporan $width`: donut dan legenda memakai alokasi yang sama" "document.querySelector('[data-allocation-amount]').dataset.allocationAmount==='30.10' && document.querySelector('[data-allocation-percentage]').dataset.allocationPercentage==='100.0000' && document.querySelector('circle[pathLength]').getAttribute('stroke-dasharray')==='100.0000 0.0000'"
            Assert-Js "Laporan $width`: filter GET berisi periode database" "document.querySelector('#periode-kas').form.method==='get' && document.querySelectorAll('#periode-kas option').length===3 && document.querySelector('#periode-kas').selectedOptions[0].textContent.includes('Januari 2030')"
            Assert-Js "Laporan $width`: tombol PDF biru tetap aktif dengan ikon" "!document.querySelector('#unduhLaporan').disabled && document.querySelector('#unduhLaporan').type==='submit' && getComputedStyle(document.querySelector('#unduhLaporan')).backgroundColor==='rgb(35, 69, 120)' && document.querySelectorAll('#unduhLaporan svg').length===1"
            Assert-Js "Laporan $width`: filter dan PDF sejajar desktop atau bertumpuk teratur mobile" "(() => {const a=document.querySelector('#filter-periode-laporan').getBoundingClientRect(),b=document.querySelector('#unduhLaporan').getBoundingClientRect();return innerWidth>=640 ? Math.abs((a.top+a.height/2)-(b.top+b.height/2))<1 && b.left>=a.right+11 : b.top>=a.bottom+11 && Math.abs(a.width-b.width)<1;})()"
            Assert-Js "Laporan $width`: judul kiri dan grup aksi kanan tidak saling menimpa" "(() => {const h=document.querySelector('#header-laporan'),a=document.querySelector('#aksi-laporan').getBoundingClientRect(),t=h.firstElementChild.getBoundingClientRect();return innerWidth<1366 ? a.top>=t.bottom+15 : a.left>=t.right+15 && a.top<t.bottom;})()"
            Assert-Js "Laporan $width`: tombol tidak pecah dan bantuan PDF tidak menambah panel" "document.querySelector('#unduhLaporan').getBoundingClientRect().height<=40 && getComputedStyle(document.querySelector('#unduhLaporan')).whiteSpace==='nowrap' && getComputedStyle(document.querySelector('#status-pdf')).position==='absolute'"
            $null = Invoke-Js "window.pdfForm=document.querySelector('#unduhLaporan').form; window.pdfCount=0; window.pdfBody=null; pdfForm.onsubmit=e=>{e.preventDefault();pdfCount++;pdfBody=Object.fromEntries(new FormData(pdfForm));};"
            Click-Element '#unduhLaporan svg'
            Assert-Js "Laporan $width`: klik mouse meminta PDF periode yang ditampilkan" "pdfCount===1 && pdfForm.method==='get' && pdfForm.action.endsWith('/laporan/pdf') && pdfBody.periode_id===document.querySelector('#periode-kas').value"
            $null = Invoke-Js "document.querySelector('#unduhLaporan').focus()"
            Press-Key 'Enter' 'Enter' 13
            Assert-Js "Laporan $width`: keyboard mengaktifkan unduhan tanpa simulasi" "pdfCount===2 && !document.querySelector('main').textContent.includes('belum tersedia')"
            $null = Invoke-Js "window.periodForm=document.querySelector('#periode-kas').form; window.periodBody=null; periodForm.onsubmit=e=>{e.preventDefault();periodBody=Object.fromEntries(new FormData(periodForm));}; document.querySelector('#periode-kas').value='semua';"
            Click-Element '#periode-kas + button'
            Assert-Js "Laporan $width`: Terapkan mengirim filter periode GET" "periodBody.periode_id==='semua' && Object.keys(periodBody).length===1"
            $null = Invoke-Js "document.querySelector('#periode-kas').value=pdfBody.periode_id"
            Assert-Js "Laporan $width`: halaman tidak meluap viewport" "document.documentElement.scrollWidth<=innerWidth+1 && document.querySelector('main').getBoundingClientRect().width<=innerWidth"
            Assert-Js "Laporan $width`: tidak ada mock saldo atau notifikasi PDF palsu" "!document.querySelector('main').textContent.includes('18.750.000') && !document.querySelector('main').textContent.includes('berhasil diunduh')"
        }
        $checks | ConvertTo-Json -Depth 10 -Compress
        return
    }
    if ($Scenario -eq 'jenis-error') {
        Wait-Js "kasorgTest.financeModals().some(m=>kasorgTest.modalOpen(m))"
        $null = Invoke-Js "window.jenisErrorModal=kasorgTest.financeModals().find(m=>kasorgTest.modalOpen(m)); window.jenisErrorForm=jenisErrorModal.querySelector('form');"
        foreach ($width in @(1366, 390)) {
            $null = Invoke-Cdp 'Emulation.setDeviceMetricsOverride' @{width=$width; height=900; deviceScaleFactor=1; mobile=($width -eq 390)}
            Assert-Js "Validasi jenis $width`: hanya modal tujuan yang terbuka otomatis" "kasorgTest.modalOpen(jenisErrorModal) && kasorgTest.financeModals().filter(m=>kasorgTest.modalOpen(m)).length===1"
            Assert-Js "Validasi jenis $width`: pesan Bahasa Indonesia tersedia" "jenisErrorModal.textContent.includes('Nominal harus lebih besar dari 0.')"
            Assert-Js "Validasi jenis $width`: nama, nominal dan deskripsi lama dipertahankan" "jenisErrorForm.querySelector('[name=nama]').value==='Koreksi tersimpan' && jenisErrorForm.querySelector('[name=nominal]').value==='0' && jenisErrorForm.querySelector('[name=deskripsi]').value==='Input belum disimpan'"
            Assert-Js "Validasi jenis $width`: CSRF dan method form tetap benar" "!!jenisErrorForm.querySelector('[name=_token]').value && (jenisErrorModal.id==='modal-jenis' ? !jenisErrorForm.querySelector('[name=_method]') : jenisErrorForm.querySelector('[name=_method]').value==='PUT')"
            Assert-Js "Validasi jenis $width`: dialog responsif" "(() => {const r=jenisErrorModal.firstElementChild.getBoundingClientRect();return r.width<=innerWidth && r.height<=innerHeight && r.left>=0;})()"
            Press-Key 'Escape' 'Escape' 27
            Assert-Js "Validasi jenis $width`: Escape menutup modal" "kasorgTest.modalClosed(jenisErrorModal)"
            $null = Invoke-Js "[...document.querySelectorAll('[data-open-modal]')].find(b=>b.dataset.openModal===jenisErrorModal.id).focus()"
            Press-Key 'Enter' 'Enter' 13
            Assert-Js "Validasi jenis $width`: keyboard membuka ulang tanpa menghapus input" "kasorgTest.modalOpen(jenisErrorModal) && jenisErrorForm.querySelector('[name=nama]').value==='Koreksi tersimpan' && jenisErrorForm.querySelector('[name=nominal]').value==='0'"
        }
        $checks | ConvertTo-Json -Depth 10 -Compress
        return
    }
    if ($Scenario -eq 'jenis') {
        foreach ($width in @(1366, 390)) {
            $null = Invoke-Cdp 'Emulation.setDeviceMetricsOverride' @{width=$width; height=900; deviceScaleFactor=1; mobile=($width -eq 390)}
            $null = Invoke-Js "window.dispatchEvent(new PageTransitionEvent('pageshow'));"
            Assert-Js "Jenis $width`: sidebar, Lucide dan tabel responsif" "getComputedStyle(document.querySelector('aside')).backgroundColor==='rgb(23, 37, 84)' && document.querySelector('aside svg')!==null && kasorgTest.responsiveTable()"
            Assert-Js "Jenis $width`: filter dan pencarian GET nyata" "document.querySelector('main form').method==='get' && !!document.querySelector('main [name=q]') && document.querySelector('main [name=status]').options.length===3"
            Click-Element 'main [data-open-modal="modal-jenis"]'
            $null = Invoke-Js "window.jenisModal=document.getElementById('modal-jenis'); window.jenisForm=jenisModal.querySelector('form'); window.jenisCount=0; window.jenisBody=null; jenisForm.onsubmit=e=>{e.preventDefault();jenisCount++;jenisBody=Object.fromEntries(new FormData(jenisForm));};"
            Assert-Js "Jenis $width`: modal tambah terbuka dengan CSRF" "kasorgTest.modalOpen(jenisModal) && jenisForm.action.includes('/admin/iuran/jenis') && !!jenisForm.querySelector('[name=_token]').value"
            $null = Invoke-Js "jenisForm.querySelector('[name=nama]').value='Uji browser'; jenisForm.querySelector('[name=nominal]').value='0';"
            Assert-Js "Jenis $width`: nominal nol ditolak browser" "!jenisForm.checkValidity() && jenisForm.querySelector('[name=nominal]').validity.rangeUnderflow"
            $null = Invoke-Js "jenisForm.querySelector('[name=nominal]').value='12.34'; jenisForm.querySelector('[name=aktif]').value='0';"
            Click-Element '#modal-jenis button[type=submit]'
            Assert-Js "Jenis $width`: submit memuat nominal presisi dan status nonaktif" "jenisCount===1 && jenisBody.nominal==='12.34' && jenisBody.aktif==='0' && !!jenisBody._token"
            Assert-Js "Jenis $width`: dialog muat pada viewport" "(() => {const r=jenisModal.firstElementChild.getBoundingClientRect();return r.width<=innerWidth && r.height<=innerHeight && r.left>=0;})()"
            Press-Key 'Escape' 'Escape' 27
            Assert-Js "Jenis $width`: Escape menutup modal dan memulihkan fokus" "kasorgTest.modalClosed(jenisModal) && document.activeElement.dataset.openModal==='modal-jenis'"
            $null = Invoke-Js "window.jenisEdit=[...document.querySelectorAll('main tbody tr')].find(r=>r.textContent.includes('Riwayat dilindungi')).querySelector('[data-open-modal^=edit-jenis-]'); jenisEdit.focus();"
            Press-Key 'Enter' 'Enter' 13
            Assert-Js "Jenis $width`: edit melalui keyboard berisi data awal dan PUT" "(() => {const m=document.getElementById(jenisEdit.dataset.openModal),f=m.querySelector('form');return kasorgTest.modalOpen(m) && f.querySelector('[name=_method]').value==='PUT' && f.querySelector('[name=nominal]').value==='75000.25' && f.querySelector('[name=nama]').value==='Iuran rutin';})()"
            Press-Key 'Escape' 'Escape' 27
            Click-Element 'main [data-open-modal^="hapus-jenis-"]'
            $null = Invoke-Js "window.jenisDelete=document.querySelector('[id^=hapus-jenis-] form'); window.jenisDeleteCount=0; jenisDelete.onsubmit=e=>{if(e.defaultPrevented)return;e.preventDefault();jenisDeleteCount++;}; jenisDelete.querySelector('[name=konfirmasi]').checked=false;"
            Assert-Js "Jenis $width`: hapus wajib konfirmasi dan DELETE" "!jenisDelete.checkValidity() && jenisDelete.querySelector('[name=_method]').value==='DELETE' && jenisDeleteCount===0"
            $null = Invoke-Js "jenisDelete.querySelector('[name=konfirmasi]').checked=true; jenisDelete.requestSubmit();"
            Assert-Js "Jenis $width`: konfirmasi memungkinkan satu submit" "jenisDeleteCount===1 && !!jenisDelete.querySelector('[name=_token]').value"
            Assert-Js "Jenis $width`: tombol dikunci setelah submit" "jenisDelete.querySelector('button[type=submit]').disabled && jenisDelete.getAttribute('aria-busy')==='true'"
            $null = Invoke-Js "jenisDelete.requestSubmit();"
            Assert-Js "Jenis $width`: submit berulang dicegah" "jenisDeleteCount===1"
            Press-Key 'Escape' 'Escape' 27
            Wait-Js "kasorgTest.financeModals().every(m=>kasorgTest.modalClosed(m))"
            Assert-Js "Jenis $width`: jenis yang memiliki tagihan tidak memiliki aksi hapus" "[...document.querySelectorAll('main tbody tr')].some(r=>r.textContent.includes('Riwayat dilindungi') && !r.querySelector('[data-open-modal^=hapus-jenis-]'))"
        }
        $checks | ConvertTo-Json -Depth 10 -Compress
        return
    }
    if ($Scenario -eq 'finance') {
        foreach ($width in @(1366, 390)) {
            $null = Invoke-Cdp 'Emulation.setDeviceMetricsOverride' @{width=$width; height=900; deviceScaleFactor=1; mobile=($width -eq 390)}
            Assert-Js "Keuangan $width`: sidebar biru dan ikon Lucide termuat" "getComputedStyle(document.querySelector('aside')).backgroundColor==='rgb(23, 37, 84)' && document.querySelectorAll('aside svg').length > 0"
            Assert-Js "Keuangan $width`: filter periode GET tersedia" "document.querySelector('#periode-kas').form.method==='get' && document.querySelectorAll('#periode-kas option').length > 0"
            Assert-Js "Keuangan $width`: tabel dan panel responsif tersedia" "kasorgTest.responsiveTable() && document.querySelector(kasorgTest.bootstrap ? 'main .ko-radius-card' : 'main .rounded-2xl') !== null"
            Assert-Js "Keuangan $width`: grafik jika ada bernilai valid, tabel transaksi dan form manual tersedia" @'
(() => { const bars=[...document.querySelectorAll('main [title^="Pemasukan"], main [title^="Pengeluaran"]')]; const donut=[...document.querySelectorAll('circle[pathLength="100"]')]; if(bars.length) return bars.every(b => Number.isFinite(parseFloat(b.style.height)) && parseFloat(b.style.height)>=0 && parseFloat(b.style.height)<=100); if(donut.length) return donut.every(c => Number.isFinite(parseFloat(c.getAttribute('stroke-dasharray')))); return document.title==='Transaksi Kas | KasOrg' && !document.querySelector('#tambahTransaksi').disabled && document.querySelector('[data-manual-transaction-form]')!==null && document.querySelectorAll('#daftarTransaksi tr').length===2; })()
'@
            Assert-Js "Keuangan $width`: angka asli tampil tanpa contoh saldo lama" "document.querySelector('main').textContent.includes('Rp 70,15') && !document.querySelector('main').textContent.includes('18.750.000')"
        }
        $checks | ConvertTo-Json -Depth 10 -Compress
        return
    }
    if ($Scenario -eq 'manual') {
        foreach ($width in @(1366, 390)) {
            $null = Invoke-Cdp 'Emulation.setDeviceMetricsOverride' @{width=$width; height=900; deviceScaleFactor=1; mobile=($width -eq 390)}
            Assert-Js "Manual $width`: branding, sidebar, tabel responsif dan Lucide" "getComputedStyle(document.querySelector('aside')).backgroundColor==='rgb(23, 37, 84)' && document.querySelector('main table').closest('.overflow-x-auto')!==null && document.querySelector('#tambahTransaksi svg')!==null"
            Click-Element '#tambahTransaksi svg'
            $null = Invoke-Js @'
window.manualModal = document.getElementById('modal-transaksi-tambah');
window.manualForm = manualModal.querySelector('form');
window.manualSubmits = 0; window.manualLastBody = null;
manualForm.onsubmit = event => {event.preventDefault(); manualSubmits++; manualLastBody=Object.fromEntries(new FormData(manualForm));};
'@
            Assert-Js "Manual $width`: modal tambah terbuka tanpa submit" "manualModal.classList.contains('flex') && manualSubmits===0 && manualForm.method==='post' && manualForm.action.includes('/admin/transaksi') && manualForm.querySelector('[name=_token]').value.length>0"
            Assert-Js "Manual $width`: kategori sesuai jenis awal" "[...manualForm.querySelector('[name=kategori_transaksi_id]').options].filter(o=>o.value&&!o.disabled).every(o=>o.dataset.jenis===manualForm.querySelector('[name=jenis]').value)"
            Assert-Js "Manual $width`: tanggal mengikuti rentang periode dan hari ini" "(() => {const d=manualForm.querySelector('[name=tanggal]'),p=manualForm.querySelector('[name=periode_id]').selectedOptions[0];return d.min===p.dataset.start && d.max<=p.dataset.end && d.max<=d.dataset.today;})()"
            $null = Invoke-Js "manualForm.querySelector('[name=jenis]').value='pengeluaran'; manualForm.querySelector('[name=jenis]').dispatchEvent(new Event('change'));"
            Assert-Js "Manual $width`: ganti jenis memfilter kategori" "[...manualForm.querySelector('[name=kategori_transaksi_id]').options].filter(o=>o.value&&!o.disabled).length>0 && [...manualForm.querySelector('[name=kategori_transaksi_id]').options].filter(o=>o.value&&!o.disabled).every(o=>o.dataset.jenis==='pengeluaran')"
            $null = Invoke-Js @'
(() => {const cat=manualForm.querySelector('[name=kategori_transaksi_id]');cat.value=[...cat.options].find(o=>o.value&&!o.disabled).value;
const date=manualForm.querySelector('[name=tanggal]');date.value=manualForm.querySelector('[name=periode_id]').selectedOptions[0].dataset.start;
manualForm.querySelector('[name=nominal]').value='-1'; manualForm.querySelector('[name=keterangan]').value='Uji browser manual';})();
'@
            Assert-Js "Manual $width`: nominal negatif ditolak browser" "!manualForm.checkValidity() && manualForm.querySelector('[name=nominal]').validity.rangeUnderflow"
            $null = Invoke-Js "manualForm.querySelector('[name=nominal]').value='12.34'"
            Click-Element '#modal-transaksi-tambah button[type=submit]'
            Assert-Js "Manual $width`: submit membawa tanggal, nominal, kategori, periode, CSRF" "manualSubmits===1 && manualLastBody.nominal==='12.34' && manualLastBody.tanggal===manualForm.querySelector('[name=tanggal]').value && manualLastBody.jenis==='pengeluaran' && !!manualLastBody.kategori_transaksi_id && !!manualLastBody.periode_id && !!manualLastBody._token"
            Assert-Js "Manual $width`: dialog muat pada viewport" "(() => {const r=manualModal.firstElementChild.getBoundingClientRect();return r.width<=innerWidth && r.height<=innerHeight && r.left>=0;})()"
            Press-Key 'Escape' 'Escape' 27
            Assert-Js "Manual $width`: Escape menutup dan mengembalikan fokus" "manualModal.classList.contains('hidden') && document.activeElement.id==='tambahTransaksi'"
            $null = Invoke-Js "document.querySelector('#tambahTransaksi').focus()"
            Press-Key 'Enter' 'Enter' 13
            Assert-Js "Manual $width`: keyboard membuka modal" "manualModal.classList.contains('flex')"
            Press-Key 'Escape' 'Escape' 27
            $null = Invoke-Js @'
window.manualEditButton=document.querySelector('#daftarTransaksi [data-open-modal^="modal-transaksi-edit-"]');
window.manualEditModal=document.getElementById(manualEditButton.dataset.openModal);
manualEditButton.focus();
'@
            Press-Key 'Enter' 'Enter' 13
            Assert-Js "Manual $width`: edit berisi nilai asli dan PUT" "(() => {const f=manualEditModal.querySelector('form');return manualEditModal.classList.contains('flex') && f.querySelector('[name=_method]').value==='PUT' && f.querySelector('[name=nominal]').value!=='' && f.querySelector('[name=kategori_transaksi_id]').selectedOptions[0].dataset.jenis===f.querySelector('[name=jenis]').value;})()"
            Press-Key 'Escape' 'Escape' 27
            $null = Invoke-Js @'
window.manualDeleteButton=document.querySelector('#daftarTransaksi [data-open-modal^="modal-transaksi-hapus-"]');
window.manualDeleteModal=document.getElementById(manualDeleteButton.dataset.openModal); manualDeleteButton.focus();
'@
            Press-Key 'Enter' 'Enter' 13
            $null = Invoke-Js "window.manualDeleteForm=manualDeleteModal.querySelector('form'); window.manualDeleteCount=0; manualDeleteForm.onsubmit=e=>{e.preventDefault();manualDeleteCount++;}; manualDeleteForm.querySelector('[name=konfirmasi]').checked=false;"
            Assert-Js "Manual $width`: hapus memerlukan konfirmasi dan DELETE" "manualDeleteModal.classList.contains('flex') && manualDeleteForm.querySelector('[name=_method]').value==='DELETE' && !manualDeleteForm.checkValidity() && manualDeleteCount===0"
            $deleteSelector = '#' + (Invoke-Js 'manualDeleteModal.id')
            Click-Element ($deleteSelector + ' [name="konfirmasi"]')
            Click-Element ($deleteSelector + ' button[type="submit"]')
            Assert-Js "Manual $width`: konfirmasi mengizinkan submit sekali dengan CSRF" "manualDeleteCount===1 && manualDeleteForm.querySelector('[name=konfirmasi]').checked && !!manualDeleteForm.querySelector('[name=_token]').value"
            Press-Key 'Escape' 'Escape' 27
            Assert-Js "Manual $width`: transaksi otomatis tidak memiliki tombol kelola" "[...document.querySelectorAll('#daftarTransaksi tr')].some(row=>row.textContent.includes('Otomatis') && !row.querySelector('[data-open-modal]'))"
        }
        $checks | ConvertTo-Json -Depth 10 -Compress
        return
    }
    if ($Scenario -eq 'login') {
        $null = Invoke-Js @'
window.loginPassword = document.getElementById('password');
window.loginToggle = document.getElementById('toggle-password');
window.loginForm = loginPassword.form;
window.loginSubmitCount = 0;
loginForm.addEventListener('submit', event => { event.preventDefault(); window.loginSubmitCount++; });
window.originalToken = loginForm.querySelector('[name="_token"]').value;
window.originalAction = loginForm.action;
window.brandIcon = document.querySelector('main section svg');
window.loginState = () => ({type:loginPassword.type, icon:loginToggle.querySelector('svg')?.getAttribute('data-lucide'), pressed:loginToggle.getAttribute('aria-pressed'), label:loginToggle.getAttribute('aria-label')});
'@
        Assert-Js 'Login awal: password tersembunyi dengan ikon eye-off' "loginState().type === 'password' && loginState().icon === 'eye-off' && loginState().pressed === 'false'"
        foreach ($width in @(1366, 390)) {
            $null = Invoke-Cdp 'Emulation.setDeviceMetricsOverride' @{width=$width; height=900; deviceScaleFactor=1; mobile=($width -eq 390)}
            $null = Invoke-Js "loginPassword.value = 'BrowserLoginTestValue!'; loginToggle.scrollIntoView({block:'center'})"
            Assert-Js "Login $width`: tombol tidak terhalang overlay dan type button" @'
(() => { const r=loginToggle.getBoundingClientRect(); const top=document.elementFromPoint(r.x+r.width/2,r.y+r.height/2); return loginToggle.contains(top) && loginToggle.type==='button' && loginToggle.tabIndex===0 && loginToggle.getAttribute('aria-controls')==='password'; })()
'@
            Click-Element '#toggle-password svg'
            Assert-Js "Login $width`: klik SVG menampilkan password dan ikon eye" "loginState().type==='text' && loginState().icon==='eye' && loginState().pressed==='true' && loginState().label==='Sembunyikan kata sandi'"
            Click-Element '#toggle-password'
            Assert-Js "Login $width`: klik berikutnya menyembunyikan password dan ikon eye-off" "loginState().type==='password' && loginState().icon==='eye-off' && loginState().pressed==='false' && loginState().label==='Tampilkan kata sandi'"
            $null = Invoke-Js 'loginPassword.focus()'
            Press-Key 'Tab' 'Tab' 9
            Assert-Js "Login $width`: Tab mencapai tombol dan fokus terlihat" "document.activeElement===loginToggle && kasorgTest.focusVisible(loginToggle)"
            Press-Key 'Enter' 'Enter' 13
            Assert-Js "Login $width`: Enter menampilkan password" "loginState().type==='text' && loginState().icon==='eye' && loginState().pressed==='true'"
            Press-Key ' ' 'Space' 32
            Assert-Js "Login $width`: Spasi menyembunyikan password" "loginState().type==='password' && loginState().icon==='eye-off' && loginState().pressed==='false'"
            for ($i=0; $i -lt 6; $i++) { Click-Element '#toggle-password svg' }
            Assert-Js "Login $width`: klik berulang tidak kehilangan listener atau menggandakan SVG" "loginState().type==='password' && loginState().icon==='eye-off' && loginToggle.querySelectorAll('svg').length===1 && window.brandIcon.isConnected"
            Assert-Js "Login $width`: nilai password tetap dan tidak ada submit" "loginPassword.value==='BrowserLoginTestValue!' && window.loginSubmitCount===0"
            Assert-Js "Login $width`: action, CSRF dan atribut autentikasi tetap" "loginForm.action===window.originalAction && loginForm.method==='post' && loginForm.querySelector('[name=_token]').value===window.originalToken && loginPassword.name==='password' && loginPassword.autocomplete==='current-password' && loginPassword.required"
        }
        $checks | ConvertTo-Json -Depth 10 -Compress
        return
    }
    Assert-Js 'Lucide SVG dan styling Bootstrap/legacy asli termuat' '!!window.lucide && document.querySelectorAll("[data-password-toggle] svg").length === 2'
    $null = Invoke-Js @'
window.toggleSubmitCount = 0;
document.getElementById('form-anggota').addEventListener('submit', e => { e.preventDefault(); window.toggleSubmitCount++; });
window.toggleInputs = ['anggota-password', 'anggota-password-confirmation'];
window.toggleButtons = toggleInputs.map(id => document.querySelector(`[data-password-toggle="${id}"]`));
window.snapshot = () => toggleInputs.map((id, i) => {
 const input = document.getElementById(id), button = toggleButtons[i];
 return {type:input.type, value:input.value, pressed:button.getAttribute('aria-pressed'), label:button.getAttribute('aria-label'), icon:button.querySelector('svg')?.getAttribute('data-lucide'), svgs:button.querySelectorAll('svg').length};
});
'@
    foreach ($width in @(1366, 390)) {
        $null = Invoke-Cdp 'Emulation.setDeviceMetricsOverride' @{width = $width; height = 900; deviceScaleFactor = 1; mobile = ($width -eq 390)}
        Click-Element '#tambah-anggota'
        Assert-Js "Viewport $width`: modal terbuka, kedua input tersembunyi dan ikon eye" "kasorgTest.modalOpen(document.getElementById('modal-anggota')) && snapshot().every(s => s.type === 'password' && s.icon === 'eye' && s.pressed === 'false' && s.svgs === 1)"
        Assert-Js "Viewport $width`: ukuran, padding, border, posisi, dan fokus konsisten" @'
(() => {
 const styles = toggleButtons.map(b => { const s = getComputedStyle(b); const r = b.getBoundingClientRect(); return [r.width,r.height,s.paddingTop,s.paddingRight,s.paddingBottom,s.paddingLeft,s.borderTopWidth,s.borderTopStyle,s.borderTopColor,s.borderRadius,s.position,s.right,s.transform,s.outlineStyle].join('|'); });
 return styles[0] === styles[1] && toggleButtons.every(b => { const r=b.getBoundingClientRect(), i=document.getElementById(b.dataset.passwordToggle).getBoundingClientRect(); return r.width>=32 && r.height>=32 && Math.abs((r.top+r.height/2)-(i.top+i.height/2))<1 && r.right<=i.right && b.getAttribute('type')==='button' && b.tabIndex===0 && b.getAttribute('aria-controls')===b.dataset.passwordToggle; });
})()
'@
        $null = Invoke-Js "toggleInputs.forEach((id,i) => document.getElementById(id).value = 'BrowserTestValue!'+i); window.outsideIcon = document.querySelector('aside svg') || document.querySelector('svg');"
        Click-Element '[data-password-toggle="anggota-password"]'
        Assert-Js "Viewport $width`: password pertama terlihat, konfirmasi tetap tersembunyi" "snapshot()[0].type === 'text' && snapshot()[0].icon === 'eye-off' && snapshot()[0].pressed === 'true' && snapshot()[0].label === 'Sembunyikan kata sandi awal' && snapshot()[1].type === 'password' && snapshot()[1].icon === 'eye'"
        Click-Element '[data-password-toggle="anggota-password-confirmation"]'
        Assert-Js "Viewport $width`: konfirmasi dapat ditampilkan secara independen" "snapshot().every(s => s.type === 'text' && s.icon === 'eye-off' && s.pressed === 'true') && snapshot()[1].label === 'Sembunyikan konfirmasi kata sandi'"
        Click-Element '[data-password-toggle="anggota-password"]'
        Assert-Js "Viewport $width`: menyembunyikan pertama tidak mengubah konfirmasi" "snapshot()[0].type === 'password' && snapshot()[0].icon === 'eye' && snapshot()[0].pressed === 'false' && snapshot()[1].type === 'text' && snapshot()[1].icon === 'eye-off'"
        $null = Invoke-Js 'toggleButtons[0].focus()'
        Press-Key 'Enter' 'Enter' 13
        Assert-Js "Viewport $width`: Enter mengaktifkan tombol berfokus" "snapshot()[0].type === 'text' && snapshot()[0].pressed === 'true' && document.activeElement === toggleButtons[0]"
        Press-Key ' ' 'Space' 32
        Assert-Js "Viewport $width`: Spasi menyembunyikan kembali" "snapshot()[0].type === 'password' && snapshot()[0].pressed === 'false'"
        $null = Invoke-Js "document.getElementById('anggota-password-confirmation').focus()"
        Press-Key 'Tab' 'Tab' 9
        Assert-Js "Viewport $width`: Tab mencapai tombol konfirmasi dan fokus terlihat" "document.activeElement === toggleButtons[1] && kasorgTest.focusVisible(toggleButtons[1])"
        Press-Key 'Enter' 'Enter' 13
        Assert-Js "Viewport $width`: Enter pada konfirmasi bekerja independen" "snapshot()[1].type === 'password' && snapshot()[1].icon === 'eye' && snapshot()[0].type === 'password'"
        Press-Key ' ' 'Space' 32
        Assert-Js "Viewport $width`: Spasi pada konfirmasi bekerja independen" "snapshot()[1].type === 'text' && snapshot()[1].icon === 'eye-off' && snapshot()[0].type === 'password'"
        Assert-Js "Viewport $width`: nilai tetap dan tidak ada submit" "snapshot().every((s,i) => s.value === 'BrowserTestValue!'+i) && window.toggleSubmitCount === 0"
        Assert-Js "Viewport $width`: hanya satu SVG per tombol, ikon lain tidak dirender ulang" 'snapshot().every(s => s.svgs === 1) && window.outsideIcon.isConnected'
        foreach ($close in @('cancel', 'x', 'escape', 'backdrop')) {
            $null = Invoke-Js "toggleButtons.filter(b => b.getAttribute('aria-pressed') === 'false').forEach(b => b.click())"
            if ($close -eq 'cancel') { Click-Element '#batal-modal-anggota' }
            elseif ($close -eq 'x') { Click-Element '#tutup-modal-anggota' }
            elseif ($close -eq 'escape') { Press-Key 'Escape' 'Escape' 27 }
            else {
                $null = Invoke-Cdp 'Input.dispatchMouseEvent' @{type='mousePressed'; x=2; y=2; button='left'; clickCount=1}
                $null = Invoke-Cdp 'Input.dispatchMouseEvent' @{type='mouseReleased'; x=2; y=2; button='left'; clickCount=1}
            }
            Assert-Js "Viewport $width`: tutup $close mereset kedua input, label, dan ikon" "kasorgTest.modalClosed(document.getElementById('modal-anggota')) && snapshot().every(s => s.type === 'password' && s.icon === 'eye' && s.pressed === 'false' && s.label.startsWith('Tampilkan') && s.svgs === 1)"
            Click-Element '#tambah-anggota'
            Assert-Js "Viewport $width`: buka ulang setelah $close tetap tersembunyi" "kasorgTest.modalOpen(document.getElementById('modal-anggota')) && snapshot().every(s => s.type === 'password' && s.value === '' && s.icon === 'eye' && s.pressed === 'false')"
        }
        Click-Element '#batal-modal-anggota'
        Wait-Js "kasorgTest.modalClosed(document.getElementById('modal-anggota'))"
    }
    $checks | ConvertTo-Json -Depth 10 -Compress
} finally {
    if ($socket) {
        try { $null = Invoke-Cdp 'Browser.close' } catch {}
        $socket.Dispose()
    }
    if ($chromeProcess -and -not $chromeProcess.HasExited) {
        if (-not $chromeProcess.WaitForExit(5000)) { Stop-Process -Id $chromeProcess.Id -ErrorAction SilentlyContinue }
    }
    $resolved = [System.IO.Path]::GetFullPath($browserRoot)
    $tempRoot = [System.IO.Path]::GetFullPath([System.IO.Path]::GetTempPath())
    if ($resolved.StartsWith($tempRoot, [System.StringComparison]::OrdinalIgnoreCase) -and (Split-Path $resolved -Leaf).StartsWith('kasorg-password-ui-')) {
        Remove-Item -LiteralPath $resolved -Recurse -Force -ErrorAction SilentlyContinue
    }
}
