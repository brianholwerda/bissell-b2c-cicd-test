copy initialize.html RefundsDashboard
copy configuration.json RefundsDashboard

powershell -command "(Get-Content 'D:\OSvC\BUI\RefundsDashboard\RefundsDashboard\index.html') -replace '/RefundsDashboard','./RefundsDashboard' | Set-Content 'D:\OSvC\BUI\RefundsDashboard\RefundsDashboard\index.html'"

powershell -command "(Get-Content 'D:\OSvC\BUI\RefundsDashboard\RefundsDashboard\RefundsDashboard5.css') -replace '/RefundsDashboard','./RefundsDashboard' | Set-Content 'D:\OSvC\BUI\RefundsDashboard\RefundsDashboard\RefundsDashboard5.css'"