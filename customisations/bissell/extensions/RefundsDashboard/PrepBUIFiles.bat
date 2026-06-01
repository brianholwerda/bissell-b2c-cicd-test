copy initialize.html RefundsDashboard
copy configuration.json RefundsDashboard

powershell -command "(Get-Content '.\RefundsDashboard\index.html') -replace '/RefundsDashboard','./RefundsDashboard' | Set-Content '.\RefundsDashboard\index.html'"

powershell -command "(Get-Content '.\RefundsDashboard\RefundsDashboard5.css') -replace '/RefundsDashboard','./RefundsDashboard' | Set-Content '.\RefundsDashboard\RefundsDashboard5.css'"