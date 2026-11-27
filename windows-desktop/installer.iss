#define AppName "اف Ai"
#define AppVersion "1.0.1"
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
Filename: "{tmp}\WebView2Setup.exe"; Parameters: "/silent /install"; StatusMsg: "در حال نصب پیش‌نیاز نمایش وب..."; Flags: runhidden waituntilterminated; Check: not IsWebView2Installed
Filename: "{app}\{#AppExe}"; Description: "اجرای اف Ai"; Flags: postinstall nowait skipifsilent

[Code]
function IsWebView2Installed(): Boolean;
var
  Version: String;
begin
  Result := RegQueryStringValue(HKLM32,
    'SOFTWARE\Microsoft\EdgeUpdate\Clients\{F3017226-FE2A-4295-8BDF-00C3A9A7E4C5}',
    'pv', Version) and (Version <> '') and (Version <> '0.0.0.0');

  if not Result then
    Result := RegQueryStringValue(HKCU,
      'Software\Microsoft\EdgeUpdate\Clients\{F3017226-FE2A-4295-8BDF-00C3A9A7E4C5}',
      'pv', Version) and (Version <> '') and (Version <> '0.0.0.0');
end;
