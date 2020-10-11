<template>
  <button :disabled="required && disabled" @click="preview">
    Vorschau öffnen
  </button>
</template>

<script>
import Vue from 'vue';

import mixin from '@directus/extension-toolkit/mixins/interface';
import DirectusSDK from '@directus/sdk-js';

const client = new DirectusSDK({
  url: 'https://admin.sat-dill.de',
  project: 'sat-dillenburg',
  storage: window.localStorage,
});

const resolvers = {
  'many-to-one': async (collection, fieldName, key) => {
    const relations = await client.getRelations({
      filter: {
        collection_many: {
          eq: collection,
        },

        field_many: {
          eq: fieldName,
        },
      },
    });

    const [relation] = relations.data;
    const $$collection = relation.collection_one;

    const data = await client.getItem($$collection, key);

    return { key: fieldName, data };
  },

  file: async (_, fieldName, key) => {
    const isNumber = typeof key === 'number';
    const data = isNumber ? await client.getFile(`${key}`) : { data: key };

    return { key: fieldName, data };
  },
};

export default Vue.extend({
  mixins: [mixin],

  methods: {
    async preview() {
      const resolvePromises = [];

      const valuesEntries = Object.entries(this.values);
      const availableResolvers = Object.keys(resolvers);

      for (let i = 0; i < valuesEntries.length; i += 1) {
        const [fieldName, newKey] = valuesEntries[i];
        const field = this.fields[fieldName];

        const $collection = field.collection;
        const $interface = field.interface;

        const resolveable = availableResolvers.includes($interface);

        if (resolveable) {
          const resolver = resolvers[$interface]($collection, fieldName, newKey);
          resolvePromises.push(resolver);
        }
      }

      const newValues = { ...this.values };
      const resolvedFields = await Promise.all(resolvePromises);

      for (let i = 0; i < resolvedFields.length; i += 1) {
        const { key, data } = resolvedFields[i];
        newValues[key] = data.data;
      }

      const url = new URL(this.options.url_template);
      const popup = window.open(this.options.url_template, '_blank');
      const interval = setInterval(
        () => popup.postMessage('ping', url.origin),
        100,
      );

      const eventHandler = (event) => {
        if (event.origin !== url.origin) return;
        if (event.data === 'pong') clearInterval(interval);

        popup.postMessage(newValues, url.origin);
        window.removeEventListener('message', eventHandler);
      };

      window.addEventListener('message', eventHandler, false);
    },
  },
});
</script>

<style lang="scss" scoped>
button {
  font-size: 16px;
  font-weight: 500;
  border-radius: 3px;
  background-color: #263238;
  color: #fff;
  padding: 14px;

  &:hover {
    background-color: #3a4c55;
  }
}
</style>
