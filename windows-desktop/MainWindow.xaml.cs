using Microsoft.Web.WebView2.Core;
using System;
using System.IO;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Media;

namespace AfAi;

public partial class MainWindow : Window
{
    public MainWindow()
    {
        InitializeComponent();
        Loaded += async (_, _) => await StartBrowserAsync();
    }

    private async Task StartBrowserAsync()
    {
        Exception? lastError = null;
        var localData = Path.Combine(
            Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData),
            "Fizer", "AfAi", "WebView2");
        var fallbackData = Path.Combine(Path.GetTempPath(), "Fizer", "AfAi", "WebView2");

        foreach (var userDataFolder in new[] { localData, fallbackData })
        {
            try
            {
                Directory.CreateDirectory(userDataFolder);
                var environment = await CoreWebView2Environment.CreateAsync(null, userDataFolder);
                await Browser.EnsureCoreWebView2Async(environment);

                Browser.CoreWebView2.Settings.AreDevToolsEnabled = false;
                Browser.CoreWebView2.Settings.IsStatusBarEnabled = false;
                Browser.CoreWebView2.Settings.AreDefaultContextMenusEnabled = true;
                Browser.CoreWebView2.Settings.IsZoomControlEnabled = true;
                Browser.CoreWebView2.NavigationCompleted += (_, args) =>
                {
                    LoadingPanel.Visibility = Visibility.Collapsed;
                    if (!args.IsSuccess)
                    {
                        ShowMessage("اتصال به Fizer.ir انجام نشد. اتصال اینترنت را بررسی کنید.");
                    }
                };
                Browser.CoreWebView2.Navigate("https://Fizer.ir");
                return;
            }
            catch (Exception ex)
            {
                lastError = ex;
            }
        }

        LoadingPanel.Visibility = Visibility.Visible;
        var details = lastError?.Message ?? "خطای ناشناخته";
        ShowMessage("اجرای موتور نمایش اف Ai با مشکل روبه‌رو شد.\n" +
                    "برنامه پوشه داده محلی و مسیر جایگزین را امتحان کرد.\n\n" + details);
    }

    private void ShowMessage(string message)
    {
        LoadingPanel.Child = new TextBlock
        {
            Text = message,
            Foreground = Brushes.White,
            FontSize = 16,
            TextWrapping = TextWrapping.Wrap,
            Margin = new Thickness(40),
            VerticalAlignment = VerticalAlignment.Center,
            HorizontalAlignment = HorizontalAlignment.Center
        };
    }
}
