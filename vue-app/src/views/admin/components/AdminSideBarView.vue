<script setup lang="ts">
import { DashBoardIcon } from '@/stores/icon'
</script>

<template>
  <div class="w-full flex flex-col justify-between h-screen">
    <div class="py-4">
      <div class="flex flex-col justify-start items-center gap-2">
        <div class="size-20 overflow-clip border border-white rounded-full bg-orange-500">
          <img
            class="rounded-full"
            src="https://img.pikbest.com/png-images/20241111/-22creative-food-logo-collection-for-culinary-brands-22_11079861.png!sw800"
            alt=""
          />
        </div>
        <div class="flex justify-start items-center gap-1">
          <h1 class="font-black text-3xl text-orange-500">Good Food</h1>
        </div>
      </div>
      <ul class="w-full p-4 space-y-1">
        <li class="w-full">
          <RouterLink :to="{ name: 'dashboard' }">
            <div
              class="w-full bg-orange-500 overflow-clip rounded-lg pl-1 group hover:scale-105 duration-500 relative"
            >
              <div
                class="w-full bg-white text-orange-500 p-3 border border-orange-500 rounded-lg flex justify-start items-center gap-2"
              >
                <component :is="DashBoardIcon" />
                <span>Dashboard</span>
              </div>
              <!-- active -->
              <div class="absolute top-0 bottom-0 right-0 flex justify-center items-center p-2">
                <div
                  class="rounded-full p-1 border duration-500 ease-in-out"
                  :class="
                    $route.name == 'dashboard'
                      ? 'border-orange-500 bg-slate-50'
                      : 'border-slate-500 bg-slate-500'
                  "
                >
                  <div
                    class="size-2 rounded-full"
                    :class="$route.name == 'dashboard' ? 'bg-orange-500' : 'bg-slate-100'"
                  ></div>
                </div>
              </div>
            </div>
          </RouterLink>
        </li>

        <li class="w-full">
          <RouterLink :to="{ name: 'food' }">
            <div
              class="w-full bg-orange-500 overflow-clip rounded-lg pl-1 group hover:scale-105 duration-500 relative"
            >
              <div
                class="w-full bg-white text-orange-500 p-3 border border-orange-500 rounded-lg flex justify-start items-center gap-2"
              >
                <component :is="DashBoardIcon" />
                <span>Foods</span>
              </div>
              <!-- active -->
              <div class="absolute top-0 bottom-0 right-0 flex justify-center items-center p-2">
                <div
                  class="rounded-full p-1 border duration-500 ease-in-out"
                  :class="
                    $route.name == 'food'
                      ? 'border-orange-500 bg-slate-50'
                      : 'border-slate-500 bg-slate-500'
                  "
                >
                  <div
                    class="size-2 rounded-full"
                    :class="$route.name == 'food' ? 'bg-orange-500' : 'bg-slate-100'"
                  ></div>
                </div>
              </div>
            </div>
          </RouterLink>
        </li>

        <!-- categories -->
        <li class="w-full">
          <RouterLink :to="{ name: 'categories' }">
            <div
              class="w-full bg-orange-500 overflow-clip rounded-lg pl-1 group hover:scale-105 duration-500 relative"
            >
              <div
                class="w-full bg-white text-orange-500 p-3 border border-orange-500 rounded-lg flex justify-start items-center gap-2"
              >
                <component :is="DashBoardIcon" />
                <span class="capitalize">categories</span>
              </div>
              <!-- active -->
              <div class="absolute top-0 bottom-0 right-0 flex justify-center items-center p-2">
                <div
                  class="rounded-full p-1 border duration-500 ease-in-out"
                  :class="
                    $route.name == 'categories'
                      ? 'border-orange-500 bg-slate-50'
                      : 'border-slate-500 bg-slate-500'
                  "
                >
                  <div
                    class="size-2 rounded-full"
                    :class="$route.name == 'categories' ? 'bg-orange-500' : 'bg-slate-100'"
                  ></div>
                </div>
              </div>
            </div>
          </RouterLink>
        </li>
        <!-- categories -->

        <!-- categories -->
        <li class="w-full">
          <RouterLink :to="{ name: 'table' }">
            <div
              class="w-full bg-orange-500 overflow-clip rounded-lg pl-1 group hover:scale-105 duration-500 relative"
            >
              <div
                class="w-full bg-white text-orange-500 p-3 border border-orange-500 rounded-lg flex justify-start items-center gap-2"
              >
                <component :is="DashBoardIcon" />
                <span class="capitalize">tables</span>
              </div>
              <!-- active -->
              <div class="absolute top-0 bottom-0 right-0 flex justify-center items-center p-2">
                <div
                  class="rounded-full p-1 border duration-500 ease-in-out"
                  :class="
                    $route.name == 'table'
                      ? 'border-orange-500 bg-slate-50'
                      : 'border-slate-500 bg-slate-500'
                  "
                >
                  <div
                    class="size-2 rounded-full"
                    :class="$route.name == 'table' ? 'bg-orange-500' : 'bg-slate-100'"
                  ></div>
                </div>
              </div>
            </div>
          </RouterLink>
        </li>
        <!-- categories -->

        <!-- order -->
        <li class="w-full">
          <RouterLink :to="{ name: 'order' }">
            <div
              class="w-full bg-orange-500 overflow-clip rounded-lg pl-1 group hover:scale-105 duration-500 relative"
            >
              <div
                class="w-full bg-white text-orange-500 p-3 border border-orange-500 rounded-lg flex justify-start items-center gap-2"
              >
                <component :is="DashBoardIcon" />
                <span class="capitalize">orders</span>
              </div>
              <!-- active -->
              <div class="absolute top-0 bottom-0 right-0 flex justify-center items-center p-2">
                <div
                  class="rounded-full p-1 border duration-500 ease-in-out"
                  :class="
                    $route.name == 'order'
                      ? 'border-orange-500 bg-slate-50'
                      : 'border-slate-500 bg-slate-500'
                  "
                >
                  <div
                    class="size-2 rounded-full"
                    :class="$route.name == 'order' ? 'bg-orange-500' : 'bg-slate-100'"
                  ></div>
                </div>
              </div>
            </div>
          </RouterLink>
        </li>
        <!-- categories -->
      </ul>
    </div>

    <div class="p-4 bg-orange-500 text-white font-open-sans text-center">
      <p>App V.01</p>
    </div>
  </div>
</template>
