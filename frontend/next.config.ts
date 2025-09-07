import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  images: {
    remotePatterns: [
      { protocol: "https", hostname: "cdn.sanity.io", port: "" },
      { protocol: "https", hostname: "lh3.googleusercontent.com", port: "" },
      { protocol: "https", hostname: "avatars.githubusercontent.com", port: "" },
      { protocol: "https", hostname: "pub-b7fd9c30cdbf439183b75041f5f71b92.r2.dev", port: "" },
    ],
  },
  experimental: {
    allowedHosts: true,
  },
  async rewrites() {
    return [
      {
        source: '/api/:path*',
        destination: 'http://0.0.0.0:8000/api/:path*',
      },
    ];
  },
};

export default nextConfig;
