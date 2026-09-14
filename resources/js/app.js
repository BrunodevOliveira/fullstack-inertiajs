import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import Layout from './pages/Layout.vue';

createInertiaApp(
  // Só é necessário caso nãso utilizemos a função inertia() em vite.config.js
  // {
  //   resolve: (name) => {
  //       const pages = import.meta.glob('./pages/**/*.vue', { eager: true });
  //       return pages[`./pages/${name}.vue`];
  //   },
  //   setup({ el, App, props, plugin }) {
  //       createApp({ render: () => h(App, props) })
  //           .use(plugin)
  //           .mount(el);
  //   },
  // }
  {
    layout: () => Layout,
    // setup({ el, App, props, plugin }) {
    //   createApp({ render: () => h(App, props) })
    //       .use(plugin)
    //       .mount(el);
    // },
  }
);

/*
 Mesmo usando o plugin inertia(), você volta a passar parâmetros caso queira customizar comportamentos avançados, como por exemplo:

  1. Adicionar Plugins do Vue (ex: Pinia, Ziggy para rotas, i18n, etc.):
    createInertiaApp({
        setup({ el, App, props, plugin }) {
            createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(pinia) // plugin customizado
                .mount(el);
        },
    });

  2. Definir título dinâmico da página:
    createInertiaApp({
        title: title => `${title} - Meu Aplicativo`,
    });

  3. Definir Layouts Globais Padrão para todas as páginas.

  Se você não precisa de nenhuma dessas customizações no momento, pode deixar apenas createInertiaApp() sem parâmetros junto com inertia() no Vite!

*/