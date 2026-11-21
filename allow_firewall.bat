@echo off
netsh advfirewall firewall add rule name="Laravel Dev 8000" dir=in action=allow protocol=TCP localport=8000
netsh advfirewall firewall show rule name="Laravel Dev 8000"
pause
