{{-- Dynamic Subsystem Sidebar Component --}}
@php
    use Modules\Software\Helpers\SubsystemDetector;

    // Detect current subsystem
    $currentSubsystem = SubsystemDetector::detectCurrentSubsystem();

    // Get subsystem configuration
    $subsystemConfig = $currentSubsystem ? SubsystemDetector::getSubsystemConfig($currentSubsystem) : null;

    // Check if subsystem is enabled
    $subsystemEnabled = $subsystemConfig && SubsystemDetector::isSubsystemEnabled($currentSubsystem, $mainSettings);
@endphp

@if($subsystemEnabled && $subsystemConfig)
    {{-- Include the specific subsystem sidebar --}}
    @includeIf($subsystemConfig['blade_path'])
@endif
