#define AppName "اف Ai"
#define AppVersion "1.0.0"
#define AppExe "اف Ai.exe"
[Setup]
AppId={{AFA12026-FIZER-AIAI-000000000001}
AppName={#AppName}
AppVersion={#AppVersion}
AppPublisher=Fizer
DefaultDirName={autopf}\Fizer\اف Ai
DefaultGroupName=اف Ai
OutputDir=..\dist
OutputBaseFilename=Af-Ai-Setup
Compression=lzma2
SolidCompression=yes
WizardStyle=modern
SetupIconFile=app.ico
ArchitecturesInstallIn64BitMode=x64
UninstallDisplayIcon={app}\{#AppExe}
[Files]
Source: "..\publish\*"; DestDir: "{app}"; Flags: recursesubdirs ignoreversion
Source: "WebView2Setup.exe"; DestDir: "{tmp}"; Flags: deleteafterinstall
[Icons]
Name: "{autoprograms}\اف Ai"; Filename: "{app}\{#AppExe}"
Name: "{autodesktop}\اف Ai"; Filename: "{app}\{#AppExe}"; Tasks: desktopicon
[Tasks]
Name: "desktopicon"; Description: "ساخت میانبر روی دسکتاپ"; GroupDescription: "میانبرها:"
[Run]
Filename: "{tmp}\WebView2Setup.exe"; Parameters: "/silent /install"; StatusMsg: "در حال نصب پیش‌نیاز نمایش وب..."; Flags: runhidden waituntilterminated
Filename: "{app}\{#AppExe}"; Description: "اجرای اف Ai"; Flags: postinstall nowait skipifsilent