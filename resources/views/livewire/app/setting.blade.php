<div class="space-y-6">
  <x-header title="Settings" subtitle="Configure your application, integrations, and branding." separator />

  <div class="grid lg:grid-cols-4 gap-6 px-0 mx-0">

    <div class="lg:col-span-1 space-y-6">
      <x-card class="p-5">
        <div class="flex items-center gap-4">
          @php($logo = $logo_url ?: asset('favicon.ico'))
          <x-avatar :image="$logo" alt="App" class="w-16 h-16 ring-2 ring-primary/20" />
          <div>
            <div class="font-semibold text-base-content/90">{{ $appName ?: $name ?: config('app.name') }}</div>
            <div class="text-sm text-base-content/60">{{ $appEnv ?: config('app.env') }}</div>
          </div>
        </div>
      </x-card>

      <x-menu class="bg-base-100 rounded-lg shadow">
        <x-menu-item title="General" icon="o-cog-6-tooth" wire:click="$set('tab', 'general')" :active="$tab === 'general'" />
        <x-menu-item title="Mail" icon="o-envelope" wire:click="$set('tab', 'mail')" :active="$tab === 'mail'" />
        <x-menu-item title="OAuth" icon="o-shield-check" wire:click="$set('tab', 'oauth')" :active="$tab === 'oauth'" />
        <x-menu-item title="Pusher" icon="o-bell" wire:click="$set('tab', 'pusher')" :active="$tab === 'pusher'" />
        <x-menu-item title="AI" icon="o-sparkles" wire:click="$set('tab', 'ai')" :active="$tab === 'ai'" />
        <x-menu-item title="Image & Branding" icon="o-photo" wire:click="$set('tab', 'image')" :active="$tab === 'image'" />
        <x-menu-item title="App" icon="o-wrench-screwdriver" wire:click="$set('tab', 'app')" :active="$tab === 'app'" />
      </x-menu>
    </div>

    <div class="lg:col-span-3 space-y-6">

      @if($tab === 'general')
        <x-card>
          <x-header title="General" subtitle="Basic organization and display settings." class="mb-5" />
          <form wire:submit="saveGeneral" class="space-y-5">
            <div class="grid md:grid-cols-2 gap-4">
              <x-input label="App/Org Name" icon="o-building-office" wire:model.defer="name" required placeholder="My Company" />
              <x-input label="Contact Email" icon="o-envelope" wire:model.defer="email" type="email" placeholder="support@example.com" />
              <x-input label="Phone" icon="o-phone" wire:model.defer="phone" placeholder="+1 555 000 111" />
              <x-input label="Website/App URL" icon="o-link" wire:model.defer="appUrl" type="url" placeholder="https://example.com" />
            </div>

            <x-input label="Hero/Image URL" icon="o-photo" wire:model.defer="image_url" type="url" placeholder="https://.../cover.jpg" />

            <x-textarea label="Address" wire:model.defer="address" rows="3" placeholder="Street, City, Country" />
            <x-textarea label="Description/Details" wire:model.defer="details" rows="3" placeholder="Short description" />
            <x-textarea label="Placeholder Text" wire:model.defer="placeHolder" rows="2" placeholder="Shown as placeholder across UI" />

            <div class="flex gap-2">
              <x-button type="submit" spinner="saveGeneral" class="btn-primary" icon="o-check">Save</x-button>
              <x-button type="button" class="btn-ghost" icon="o-arrow-path" wire:click="$refresh">Reset</x-button>
            </div>
          </form>
        </x-card>
      @endif

      @if($tab === 'mail')
        <x-card>
          <x-header title="Mail" subtitle="Outgoing email settings." class="mb-5" />
          <form wire:submit="saveMail" class="space-y-5">
            <div class="grid md:grid-cols-2 gap-4">
              <x-input label="Mailer" wire:model.defer="mailMailer" placeholder="smtp" />
              <x-input label="Host" wire:model.defer="mailHost" placeholder="smtp.mailtrap.io" />
              <x-input label="Port" wire:model.defer="mailPort" placeholder="587" />
              <x-input label="Username" wire:model.defer="mailUsername" />
              <x-input label="Password" wire:model.defer="mailPassword" type="password" />
              <x-input label="Encryption" wire:model.defer="mailEncryption" placeholder="tls" />
              <x-input label="From Address" wire:model.defer="mailFromAddress" type="email" />
              <x-input label="From Name" wire:model.defer="mailFromName" />
            </div>
            <div class="flex gap-2">
              <x-button type="submit" spinner="saveMail" class="btn-primary" icon="o-check">Save</x-button>
            </div>
          </form>
        </x-card>
      @endif

      @if($tab === 'oauth')
        <x-card>
          <x-header title="OAuth" subtitle="Social providers credentials." class="mb-5" />
          <form wire:submit="saveOauth" class="space-y-5">
            <x-header title="GitHub" level="3" class="mb-2" />
            <div class="grid md:grid-cols-2 gap-4">
              <x-input label="Client ID" wire:model.defer="githubClientId" />
              <x-input label="Client Secret" wire:model.defer="githubClientSecret" type="password" />
            </div>
            <x-hr class="my-4" />
            <x-header title="Google" level="3" class="mb-2" />
            <div class="grid md:grid-cols-2 gap-4">
              <x-input label="Client ID" wire:model.defer="googleClientId" />
              <x-input label="Client Secret" wire:model.defer="googleClientSecret" type="password" />
            </div>
            <div class="flex gap-2 mt-4">
              <x-button type="submit" spinner="saveOauth" class="btn-primary" icon="o-check">Save</x-button>
            </div>
          </form>
        </x-card>
      @endif

      @if($tab === 'pusher')
        <x-card>
          <x-header title="Pusher & WebPush" subtitle="Realtime broadcasting and push." class="mb-5" />
          <form wire:submit="savePusher" class="space-y-5">
            <div class="grid md:grid-cols-2 gap-4">
              <x-input label="App ID" wire:model.defer="pusherAppId" />
              <x-input label="Key" wire:model.defer="pusherAppKey" />
              <x-input label="Secret" wire:model.defer="pusherAppSecret" type="password" />
              <x-input label="Cluster" wire:model.defer="pusherAppCluster" placeholder="mt1" />
              <x-input label="Host" wire:model.defer="pusherHost" />
              <x-input label="Port" wire:model.defer="pusherPort" />
              <x-input label="Scheme" wire:model.defer="pusherScheme" placeholder="https" />
            </div>
            <x-hr class="my-4" />
            <div class="grid md:grid-cols-2 gap-4">
              <x-input label="VAPID Public Key" wire:model.defer="vapidPublicKey" />
              <x-input label="VAPID Private Key" wire:model.defer="vapidPrivateKey" type="password" />
            </div>
            <div class="flex gap-2">
              <x-button type="submit" spinner="savePusher" class="btn-primary" icon="o-check">Save</x-button>
            </div>
          </form>
        </x-card>
      @endif

      @if($tab === 'ai')
        <x-card>
          <x-header title="AI Providers" subtitle="API credentials for AI services." class="mb-5" />
          <form wire:submit="saveAi" class="space-y-6">
            <div>
              <x-header title="OpenRouter" level="3" class="mb-2" />
              <div class="grid md:grid-cols-2 gap-4">
                <x-input label="API Key" wire:model.defer="openrouterApiKey" type="password" />
                <x-input label="Base URL" wire:model.defer="openrouterBaseUrl" placeholder="https://openrouter.ai/api/v1" />
              </div>
            </div>
            <div>
              <x-header title="Gemini" level="3" class="mb-2" />
              <x-input label="API Key" wire:model.defer="geminiApiKey" type="password" />
            </div>
            <div>
              <x-header title="Pollination" level="3" class="mb-2" />
              <x-input label="API Key" wire:model.defer="pollinationApiKey" type="password" />
            </div>
            <div>
              <x-header title="OpenAI" level="3" class="mb-2" />
              <div class="grid md:grid-cols-2 gap-4">
                <x-input label="API Key" wire:model.defer="openaiApiKey" type="password" />
                <x-input label="Organization" wire:model.defer="openaiOrg" />
                <x-input label="Base URL" wire:model.defer="openaiBaseUrl" placeholder="https://api.openai.com/v1" class="md:col-span-2" />
              </div>
            </div>
            <div class="flex gap-2">
              <x-button type="submit" spinner="saveAi" class="btn-primary" icon="o-check">Save</x-button>
            </div>
          </form>
        </x-card>
      @endif

      @if($tab === 'image')
        <x-card class="relative">
          <div wire:loading.flex wire:target="logoImage,iconImage,saveBranding" class="absolute inset-0 z-10 bg-base-100/70 backdrop-blur-sm items-center justify-center rounded-lg">
            <div class="flex items-center gap-3">
              <x-loading class="loading-spinner loading-lg text-primary" />
              <span class="text-sm">Processing images...</span>
            </div>
          </div>

          <x-header title="Branding" subtitle="Logo and app icon." class="mb-5" />

          <div class="grid md:grid-cols-2 gap-6">
            <div class="space-y-3">
              <x-file label="Upload Logo" wire:model="logoImage" accept="image/*" crop-after-change>
                <x-avatar :image="$logoImage?->temporaryUrl() ?? $logo_url" alt="Logo" class="w-24 h-24 ring-4 ring-primary/20" />
              </x-file>
              <x-input label="Logo URL" wire:model.defer="logoImageUrl" type="url" placeholder="https://.../logo.png" />
            </div>
            <div class="space-y-3">
              <x-file label="Upload Icon" wire:model="iconImage" accept="image/*" crop-after-change>
                <x-avatar :image="$iconImage?->temporaryUrl() ?? $icon_url" alt="Icon" class="w-24 h-24 ring-4 ring-primary/20" />
              </x-file>
              <x-input label="Icon URL" wire:model.defer="iconImageUrl" type="url" placeholder="https://.../icon.png" />
            </div>
          </div>

          <div class="mt-4 flex flex-wrap gap-2">
            <x-button class="btn-primary" icon="o-check" wire:click="saveBranding" spinner="saveBranding">Save images</x-button>
            <x-button class="btn-ghost" icon="o-x-mark" wire:click="$set('logoImage', null); $set('iconImage', null); $set('logoImageUrl', ''); $set('iconImageUrl', '')">Clear</x-button>
          </div>
        </x-card>
      @endif

      @if($tab === 'app')
        <x-card>
          <x-header title="Application" subtitle="Core app configuration (stored in DB)." class="mb-5" />
          <form wire:submit="saveApp" class="space-y-5">
            <div class="grid md:grid-cols-2 gap-4">
              <x-input label="App Name" wire:model.defer="appName" />
              <x-input label="Environment" wire:model.defer="appEnv" placeholder="production" />
              <x-select label="Debug" wire:model.defer="appDebug" :options="[['id'=>'true','name'=>'true'],['id'=>'false','name'=>'false']]" placeholder="Select" />
              <x-input label="App URL" wire:model.defer="appUrl" type="url" />
              <x-input label="Locale" wire:model.defer="appLocale" placeholder="en" />
              <x-input label="Timezone" wire:model.defer="appTimezone" placeholder="UTC" />
              <x-input label="Queue Connection" wire:model.defer="queueConnection" placeholder="sync" />
            </div>
            <div class="flex gap-2">
              <x-button type="submit" spinner="saveApp" class="btn-primary" icon="o-check">Save</x-button>
            </div>
          </form>
        </x-card>
      @endif

    </div>

  </div>
  <div class="space-y-6">
    <x-card title="⚙️ Artisan Command Runner" class="shadow-xl">

      <form wire:submit="run" class="space-y-4">

        <div class="flex flex-col md:flex-row md:items-end gap-3">

          <div class="flex-grow">
            <x-select
              label="Select Command"
              wire:model.live="selectedCommand"
              :options="$availableCommands"
              placeholder="Choose a command to execute..."
            />
          </div>

          <div>
            <x-button

              type="submit"
              spinner="run"
              class="w-full md:w-auto"
              icon-right="o-play"
              :disabled="!$selectedCommand"
            >
              Run Command
            </x-button>
          </div>
        </div>
      </form>

      <x-hr />

      <div>
        <div class="flex justify-between items-center mb-2">
          <h3 class="text-sm font-semibold">Command Output</h3>
          <x-button
            wire:click="$set('output', null)"
            icon="o-x-mark"
            class="btn-xs btn-ghost"
            :disabled="!$output"
          />
        </div>

        <div
          @class([
              'p-4 rounded-lg font-mono text-sm whitespace-pre-wrap min-h-[150px] transition-all',
              'bg-gray-800 text-gray-400' => !$output, // Placeholder
              'bg-gray-900 text-green-400' => $output,
          ])
        >
          <div wire:loading.flex wire:target="run" class="items-center opacity-75">
            <x-loading class="loading-spinner loading-xs mr-2" />
            Executing `{{ $selectedCommand }}`...
          </div>

          <div wire:loading.remove wire:target="run">
            @if ($output)
              {!! nl2br(e($output)) !!}
            @else
              <span class="opacity-50">$ Output will appear here...</span>
            @endif
          </div>
        </div>
      </div>
    </x-card>
  </div>
</div>

