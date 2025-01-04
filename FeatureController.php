<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateFeatureRequest;
use App\Models\ImageSetting;
use App\Settings\WebhusetSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Settings;
use ZipArchive;

class FeatureController extends Controller
{
    public function Index()
    {
        // check if the icons directory exists 'assets/images/icons'
        if (File::exists(public_path('assets/images/icons'))) {
            $images = (File::files(public_path('assets/images/icons')));

            // sirt by size
            usort($images, function ($a, $b) {
                return $a->getSize() <=> $b->getSize();
            });

            // map images to filename and path
            $icons = array_map(function ($image) {
                return [
                    'name' => $image->getFilename(),
                    'path' => '/assets/images/icons/'.$image->getFilename(),
                ];
            }, $images);
        } else {
            $icons = [];
        }

        return Inertia::render('Settings/FeatureSettings', [
            'settings' => app(WebhusetSettings::class),
            'icons' => $icons,
        ]);
    }

    public function update(UpdateFeatureRequest $request)
    {

        // // Retrieve all existing settings and iterate through them
        $settings = app(WebhusetSettings::class);

        $settings->machines_enabled = $request->machines_enabled ?? $settings->machines_enabled;
        $settings->machine_name_singular = $request->machine_name_singular ?? $settings->machine_name_singular;
        $settings->machine_name_plural = $request->machine_name_plural ?? $settings->machine_name_plural;
        $settings->expenses_enabled = $request->expenses_enabled ?? $settings->expenses_enabled;
        $settings->most_used_products_enabled = $request->most_used_products_enabled ?? $settings->most_used_products_enabled;
        $settings->desired_margin_percentage_enabled = $request->desired_margin_percentage_enabled ?? $settings->desired_margin_percentage_enabled;
        $settings->weather_enabled = $request->weather_enabled ?? $settings->weather_enabled;

        $settings->save();

        // // After updating the settings, redirect the user back to the previous page
        // // This provides feedback that the operation was completed
        return redirect()->back();
    }

    public function uploadIcons(Request $request)
    {

        // Validate that a file is uploaded and it's a zip file
        $request->validate([
            'zip' => 'required|file|mimes:zip',
        ]);

        // Get the uploaded file
        $file = $request->file('zip');

        // Define the path to the icons directory
        $iconsPath = public_path('assets/images/icons');

        // Step 1: Clear the directory by deleting all files in it
        if (File::exists($iconsPath)) {

            File::deleteDirectory($iconsPath);
        }

        // Recreate the directory after clearing
        File::makeDirectory($iconsPath, 0755, true);

        // Step 2: Extract the ZIP file to the icons directory
        $zip = new ZipArchive;

        if ($zip->open($file->getRealPath()) === true) {
            // Loop through each file in the ZIP
            for ($i = 0; $i < $zip->numFiles; $i++) {
                // Get the file's name
                $filename = $zip->getNameIndex($i);

                // Check if the file is inside the 'ios/' directory
                if (strpos($filename, 'ios/') === 0) {
                    // Read the content of the file from the zip
                    $fileContent = $zip->getFromIndex($i);

                    // Remove 'ios/' from the path to extract the file into iconsPath
                    $relativePath = str_replace('ios/', '', $filename);

                    // Define the full path where the file will be saved
                    $destinationPath = $iconsPath.'/'.$relativePath;

                    // Make sure any subdirectories are created
                    File::ensureDirectoryExists(dirname($destinationPath));

                    // Write the file's content to the destination path
                    File::put($destinationPath, $fileContent);
                }
            }

            $zip->close();
        } else {
            // Handle error in case the zip file couldn't be opened
            return redirect()->back();
        }

        // Return success response
        return redirect()->back();
    }

    public function uploadLogo(Request $request)
    {
        // Validate that a file is uploaded and it's an image
        $request->validate([
            'logo' => 'required|image',
        ]);

        // Get the uploaded file
        $file = $request->file('logo');

        $setting = ImageSetting::where('name', 'logo')->first();

        if ($setting) {

            $setting->addMediaFromRequest('logo')
                ->toMediaCollection('sitelogo');

        } else {
            $setting = ImageSetting::create([
                'name' => 'logo',
            ]);

            $setting->addMediaFromRequest('logo')
                ->toMediaCollection('sitelogo');
        }

        // Return success response
        return redirect()->back();
    }
}
