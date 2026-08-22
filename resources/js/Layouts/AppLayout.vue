<template>
    <Head :title="title" />
    
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100/50">
        <!-- Navbar -->
        <nav class="bg-white/80 backdrop-blur-md border-b border-gray-200/30 sticky top-0 z-50 shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo - Ahora es el elemento principal a la izquierda -->
                    <div class="flex items-center gap-8">
                        <Link :href="'/dashboard'" class="flex items-center gap-3 group">
                            <div class="relative">
                                <img 
                                    :src="logoImage"
                                    alt="RIC Logo" 
                                    class="h-12 w-auto object-contain transition-transform duration-300 group-hover:scale-105"
                                    @error="handleLogoError"
                                    v-if="logoExists"
                                />
                                <div v-else class="h-12 w-12 bg-gradient-to-br from-[#0A1628] to-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-xl">
                                    RIC
                                </div>
                                <div class="absolute -bottom-1 left-0 w-0 h-0.5 bg-gradient-to-r from-[#0A1628] to-blue-600 transition-all duration-300 group-hover:w-full"></div>
                            </div>
                        </Link>

                        <!-- Menú de navegación desktop con PERMISOS -->
                        <div class="hidden md:flex items-center gap-1 ml-4">

                            <!-- Movimientos -->
                            <Link 
                                v-if="permisos?.puede_ver"
                                :href="'/movimientos'"
                                class="relative px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300"
                                :class="[
                                    $page.url.startsWith('/movimientos') 
                                        ? 'text-[#0A1628] bg-[#0A1628]/5' 
                                        : 'text-gray-600 hover:text-[#0A1628] hover:bg-[#0A1628]/5'
                                ]"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                    </svg>
                                    Movimientos
                                </span>
                                <span v-if="$page.url.startsWith('/movimientos')" 
                                      class="absolute bottom-0 left-1/2 -translate-x-1/2 w-6 h-0.5 bg-[#0A1628] rounded-full"></span>
                            </Link>

                            <!-- Personas -->
                            <Link 
                                v-if="permisos?.puede_ver_personas"
                                :href="'/personas'"
                                class="relative px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300"
                                :class="[
                                    $page.url.startsWith('/personas') 
                                        ? 'text-[#0A1628] bg-[#0A1628]/5' 
                                        : 'text-gray-600 hover:text-[#0A1628] hover:bg-[#0A1628]/5'
                                ]"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Personas
                                </span>
                                <span v-if="$page.url.startsWith('/personas')" 
                                      class="absolute bottom-0 left-1/2 -translate-x-1/2 w-6 h-0.5 bg-[#0A1628] rounded-full"></span>
                            </Link>

                            <!-- Cuentas -->
                            <Link 
                                v-if="permisos?.puede_ver_cuentas"
                                :href="'/cuentas'"
                                class="relative px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300"
                                :class="[
                                    $page.url.startsWith('/cuentas') 
                                        ? 'text-[#0A1628] bg-[#0A1628]/5' 
                                        : 'text-gray-600 hover:text-[#0A1628] hover:bg-[#0A1628]/5'
                                ]"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    Cuentas
                                </span>
                                <span v-if="$page.url.startsWith('/cuentas')" 
                                      class="absolute bottom-0 left-1/2 -translate-x-1/2 w-6 h-0.5 bg-[#0A1628] rounded-full"></span>
                            </Link>

                            <!-- Usuarios - Solo ADMINISTRADOR, AUDITOR, SUPERUSUARIO -->
                            <Link 
                                v-if="permisos?.puede_ver_usuarios"
                                :href="'/usuarios'"
                                class="relative px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300"
                                :class="[
                                    $page.url.startsWith('/usuarios') 
                                        ? 'text-[#0A1628] bg-[#0A1628]/5' 
                                        : 'text-gray-600 hover:text-[#0A1628] hover:bg-[#0A1628]/5'
                                ]"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    Usuarios
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold">Admin</span>
                                </span>
                                <span v-if="$page.url.startsWith('/usuarios')" 
                                      class="absolute bottom-0 left-1/2 -translate-x-1/2 w-6 h-0.5 bg-[#0A1628] rounded-full"></span>
                            </Link>

                            <!-- Empresas - Solo SUPERUSUARIO -->
                            <Link 
                                v-if="permisos?.puede_ver_empresas"
                                :href="'/empresas'"
                                class="relative px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300"
                                :class="[
                                    $page.url.startsWith('/empresas') 
                                        ? 'text-[#0A1628] bg-[#0A1628]/5' 
                                        : 'text-gray-600 hover:text-[#0A1628] hover:bg-[#0A1628]/5'
                                ]"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    Empresas
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-purple-100 text-purple-700 font-bold">Super</span>
                                </span>
                                <span v-if="$page.url.startsWith('/empresas')" 
                                      class="absolute bottom-0 left-1/2 -translate-x-1/2 w-6 h-0.5 bg-[#0A1628] rounded-full"></span>
                            </Link>

                            <!-- Reportes -->
                            <Link 
                                v-if="permisos?.puede_ver_reportes"
                                :href="'/reportes'"
                                class="relative px-3 py-2 rounded-lg text-sm font-medium transition-all duration-300"
                                :class="[
                                    $page.url.startsWith('/reportes') 
                                        ? 'text-[#0A1628] bg-[#0A1628]/5' 
                                        : 'text-gray-600 hover:text-[#0A1628] hover:bg-[#0A1628]/5'
                                ]"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    Reportes
                                </span>
                                <span v-if="$page.url.startsWith('/reportes')" 
                                      class="absolute bottom-0 left-1/2 -translate-x-1/2 w-6 h-0.5 bg-[#0A1628] rounded-full"></span>
                            </Link>
                        </div>
                    </div>

                    <!-- Usuario y acciones -->
                    <div class="flex items-center gap-2">
                        <!-- Dropdown de usuario - SOLO CERRAR SESIÓN -->
                        <Dropdown align="right" width="56" class="user-dropdown">
                            <template #trigger>
                                <button class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-[#0A1628]/5 transition-all duration-300 group border border-transparent hover:border-gray-200/50">
                                    <div class="w-8 h-8 bg-gradient-to-br from-[#0A1628] to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-md transition-transform duration-300 group-hover:scale-105 ring-2 ring-[#0A1628]/10 group-hover:ring-[#0A1628]/20">
                                        {{ userInitials }}
                                    </div>
                                    <div class="hidden lg:block">
                                        <p class="text-sm font-medium text-[#0A1628] leading-tight">
                                            {{ $page.props.auth.user?.nombre_completo || 'Usuario' }}
                                        </p>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400 transition-all duration-300 group-hover:text-[#0A1628] ml-1" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <div class="py-1 bg-white min-w-[200px]">
                                    <!-- Header del dropdown -->
                                    <div class="px-4 py-4 bg-gradient-to-r from-[#0A1628] to-[#1a2a4a] relative overflow-hidden">
                                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
                                        <div class="absolute bottom-0 left-0 w-24 h-24 bg-blue-400/10 rounded-full blur-2xl translate-y-1/2 -translate-x-1/2"></div>
                                        
                                        <div class="relative flex items-center gap-3">
                                            <div class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center text-white font-semibold text-xl border-2 border-white/20 shadow-lg">
                                                {{ userInitials }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-white truncate">
                                                    {{ $page.props.auth.user?.nombre_completo || 'Usuario' }}
                                                </p>
                                                <p class="text-xs text-blue-200/80 truncate">
                                                    {{ $page.props.auth.user?.email || '' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ✅ SOLO CERRAR SESIÓN - ELIMINADO MI PERFIL Y CONFIGURACIÓN -->
                                    <div class="py-1">
                                        <!-- Cerrar sesión -->
                                        <DropdownLink :href="'/logout'" method="post" as="button" 
                                                      class="text-red-600 hover:bg-red-50/80 mt-1">
                                            <span class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center text-red-500 group-hover:bg-red-600 group-hover:text-white transition-all duration-200">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                </svg>
                                            </span>
                                            <span class="font-medium group-hover:text-red-700">Cerrar Sesión</span>
                                        </DropdownLink>
                                    </div>
                                </div>
                            </template>
                        </Dropdown>

                        <!-- Menú móvil -->
                        <button @click="mobileMenuOpen = !mobileMenuOpen" 
                                class="md:hidden p-2 text-gray-500 hover:text-[#0A1628] hover:bg-[#0A1628]/5 rounded-lg transition-all duration-300 ml-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Menú móvil con PERMISOS -->
                <transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="max-h-0 opacity-0"
                    enter-to-class="max-h-[500px] opacity-100"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="max-h-[500px] opacity-100"
                    leave-to-class="max-h-0 opacity-0"
                >
                    <div v-if="mobileMenuOpen" class="md:hidden overflow-hidden">
                        <div class="py-3 border-t border-gray-200/30 space-y-1">
                            <!-- Dashboard -->
                            <Link
                                :href="'/dashboard'"
                                class="block px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 hover:translate-x-1"
                                :class="[
                                    $page.url.startsWith('/dashboard') 
                                        ? 'text-[#0A1628] bg-[#0A1628]/5' 
                                        : 'text-gray-600 hover:text-[#0A1628] hover:bg-[#0A1628]/5'
                                ]"
                                @click="mobileMenuOpen = false"
                            >
                                <span class="flex items-center gap-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/>
                                    </svg>
                                    Dashboard
                                </span>
                            </Link>

                            <!-- Movimientos -->
                            <Link
                                v-if="permisos?.puede_ver"
                                :href="'/movimientos'"
                                class="block px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 hover:translate-x-1"
                                :class="[
                                    $page.url.startsWith('/movimientos') 
                                        ? 'text-[#0A1628] bg-[#0A1628]/5' 
                                        : 'text-gray-600 hover:text-[#0A1628] hover:bg-[#0A1628]/5'
                                ]"
                                @click="mobileMenuOpen = false"
                            >
                                <span class="flex items-center gap-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                    </svg>
                                    Movimientos
                                </span>
                            </Link>

                            <!-- Personas -->
                            <Link
                                v-if="permisos?.puede_ver_personas"
                                :href="'/personas'"
                                class="block px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 hover:translate-x-1"
                                :class="[
                                    $page.url.startsWith('/personas') 
                                        ? 'text-[#0A1628] bg-[#0A1628]/5' 
                                        : 'text-gray-600 hover:text-[#0A1628] hover:bg-[#0A1628]/5'
                                ]"
                                @click="mobileMenuOpen = false"
                            >
                                <span class="flex items-center gap-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Personas
                                </span>
                            </Link>

                            <!-- Cuentas -->
                            <Link
                                v-if="permisos?.puede_ver_cuentas"
                                :href="'/cuentas'"
                                class="block px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 hover:translate-x-1"
                                :class="[
                                    $page.url.startsWith('/cuentas') 
                                        ? 'text-[#0A1628] bg-[#0A1628]/5' 
                                        : 'text-gray-600 hover:text-[#0A1628] hover:bg-[#0A1628]/5'
                                ]"
                                @click="mobileMenuOpen = false"
                            >
                                <span class="flex items-center gap-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    Cuentas
                                </span>
                            </Link>

                            <!-- Usuarios -->
                            <Link
                                v-if="permisos?.puede_ver_usuarios"
                                :href="'/usuarios'"
                                class="block px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 hover:translate-x-1"
                                :class="[
                                    $page.url.startsWith('/usuarios') 
                                        ? 'text-[#0A1628] bg-[#0A1628]/5' 
                                        : 'text-gray-600 hover:text-[#0A1628] hover:bg-[#0A1628]/5'
                                ]"
                                @click="mobileMenuOpen = false"
                            >
                                <span class="flex items-center gap-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    Usuarios
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold ml-auto">Admin</span>
                                </span>
                            </Link>

                            <!-- Empresas -->
                            <Link
                                v-if="permisos?.puede_ver_empresas"
                                :href="'/empresas'"
                                class="block px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 hover:translate-x-1"
                                :class="[
                                    $page.url.startsWith('/empresas') 
                                        ? 'text-[#0A1628] bg-[#0A1628]/5' 
                                        : 'text-gray-600 hover:text-[#0A1628] hover:bg-[#0A1628]/5'
                                ]"
                                @click="mobileMenuOpen = false"
                            >
                                <span class="flex items-center gap-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    Empresas
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-purple-100 text-purple-700 font-bold ml-auto">Super</span>
                                </span>
                            </Link>

                            <!-- Reportes -->
                            <Link
                                v-if="permisos?.puede_ver_reportes"
                                :href="'/reportes'"
                                class="block px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 hover:translate-x-1"
                                :class="[
                                    $page.url.startsWith('/reportes') 
                                        ? 'text-[#0A1628] bg-[#0A1628]/5' 
                                        : 'text-gray-600 hover:text-[#0A1628] hover:bg-[#0A1628]/5'
                                ]"
                                @click="mobileMenuOpen = false"
                            >
                                <span class="flex items-center gap-3">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    Reportes
                                </span>
                            </Link>
                        </div>
                    </div>
                </transition>
            </div>
        </nav>

        <!-- Page Heading -->
        <header v-if="$slots.header" class="bg-white/80 backdrop-blur-sm border-b border-gray-200/30 shadow-sm">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main class="py-6">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { Link, usePage, Head } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

// ✅ IMPORTAR EL LOGO DESDE resources/js/images/logo.png
import logoImage from '@/images/logo.png';

// Definir props para el título
const props = defineProps({
    title: {
        type: String,
        default: 'RIC - Sistema de Gestión'
    }
});


// ✅ AGREGAR ESTO PARA DEBUG
console.log('🔍 AppLayout montado', {
    url: usePage().url,
    props: usePage().props
});

// ✅ WATCH PARA VER CAMBIOS DE URL
watch(() => usePage().url, (newUrl) => {
    console.log('🔄 Navegación detectada en AppLayout:', newUrl);
});

const page = usePage();
const mobileMenuOpen = ref(false);
const logoExists = ref(true);

// ✅ OBTENER PERMISOS DESDE EL BACKEND
const permisos = computed(() => page.props.permisos || {});
const tipoUsuario = computed(() => page.props.auth?.user?.tipo_usuario || '');

const handleLogoError = () => {
    logoExists.value = false;
};

// ✅ Iniciales del usuario para el avatar
const userInitials = computed(() => {
    const name = page.props.auth?.user?.nombre_completo || '';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});
</script>

<style scoped>
/* Estilos personalizados */
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

.animate-pulse {
    animation: pulse 2s ease-in-out infinite;
}

/* Efecto de glassmorphism mejorado */
.backdrop-blur-md {
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

/* Transición suave para el menú móvil */
.max-h-0 {
    max-height: 0;
}

.max-h-\[500px\] {
    max-height: 500px;
}

/* Efecto hover en los items del menú */
.hover\:translate-x-1:hover {
    transform: translateX(0.25rem);
}

/* Mejoras para el dropdown - mejor alineación */
:deep(.user-dropdown) {
    position: relative;
}

:deep(.user-dropdown .absolute) {
    margin-top: 0.5rem;
    right: 0;
    transform-origin: top right;
}

:deep(.user-dropdown .rounded-2xl) {
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

/* Mejora el hover de los items */
:deep(.dropdown-link) {
    padding: 0.625rem 1rem;
    transition: all 0.2s ease;
}

:deep(.dropdown-link:hover) {
    background-color: rgba(10, 22, 40, 0.05);
}

/* Decoración del header del dropdown */
:deep(.bg-gradient-to-r) {
    position: relative;
    overflow: hidden;
}

/* Mejora la apariencia general */
:deep(.shadow-2xl) {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Estilos para los íconos SVG en el dropdown */
:deep(.dropdown-link svg) {
    transition: all 0.2s ease;
}

:deep(.dropdown-link:hover svg) {
    transform: scale(1.1);
}

/* Mejora el espaciado del trigger */
:deep(.user-dropdown .cursor-pointer) {
    display: flex;
    align-items: center;
}

/* Sombra sutil en el trigger al hacer hover */
:deep(.user-dropdown .group:hover) {
    box-shadow: 0 2px 8px rgba(10, 22, 40, 0.06);
}

/* Animación del icono de flecha */
:deep(.user-dropdown .group svg:last-child) {
    transition: transform 0.3s ease;
}

:deep(.user-dropdown .group:hover svg:last-child) {
    transform: rotate(180deg);
}
</style>