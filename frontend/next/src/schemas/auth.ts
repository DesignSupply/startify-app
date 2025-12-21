import { z } from 'zod';

export const signinSchema = z.object({
  email: z.string({ required_error: 'メールは必須です' }).email('メール形式が正しくありません'),
  password: z.string({ required_error: 'パスワードは必須です' }).min(8, { message: '8文字以上で入力してください' }),
});

export type SigninSchema = z.infer<typeof signinSchema>;
