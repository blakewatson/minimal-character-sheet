import '@vue/runtime-core';

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $t(key: string): string;
    $getLocale(): string;
    $setLocale(locale: string): void;
    $signedNumString(num: number): string;
  }
}

export {};
