import { InertiaForm, useForm as inertiaUseForm } from '@inertiajs/vue3';

export default {
    install: () => {
        window['useForm'] = inertiaUseForm;
    },
};

export type FormReturnType<TForm extends object> = Prettify<
    Omit<InertiaForm<TForm>, 'errors'> & {
        errors: Prettify<Partial<Record<keyof TForm, string>> & Partial<Record<PossibleErrorKeys<TForm>, string>>>;
    }
>;

type Prettify<T> = {
    [K in keyof T]: T[K];
} & NonNullable<unknown>;

type IsTuple<T extends ReadonlyArray<unknown>> = number extends T['length'] ? false : true;
type TupleKeys<T extends ReadonlyArray<unknown>> = Exclude<keyof T, keyof unknown[]>;

type IsEqual<T1, T2> = T1 extends T2 ? ((<G>() => G extends T1 ? 1 : 2) extends <G>() => G extends T2 ? 1 : 2 ? true : false) : false;

type AnyIsEqual<T1, T2> = T1 extends T2 ? (IsEqual<T1, T2> extends true ? true : never) : never;

type PathImpl<K extends string | number, V, TraversedTypes> = V extends string | number | boolean | null | undefined
    ? `${K}`
    : true extends AnyIsEqual<TraversedTypes, V>
    ? `${K}`
    : `${K}` | `${K}.${PathInternal<V, TraversedTypes | V>}`;

type PathInternal<T, TraversedTypes = T> = T extends ReadonlyArray<infer V>
    ? IsTuple<T> extends true
        ? {
              [K in TupleKeys<T>]-?: PathImpl<K & string, T[K], TraversedTypes>;
          }[TupleKeys<T>]
        : PathImpl<`\${index}`, V, TraversedTypes> | PathImpl<number, V, TraversedTypes>
    : {
          [K in keyof T]-?: PathImpl<K & string, T[K], TraversedTypes>;
      }[keyof T];

type Path<T> = T extends unknown ? PathInternal<T> : never;

type PossibleErrorKeys<TForm extends object> = Path<TForm>;
